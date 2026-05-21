<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotDataController extends Controller
{
    // ── PROXY ─────────────────────────────────────────────────────────────────
    // Browser → POST /api/chatbot/message → Node.js chatbot → response
    // Keeps the Node.js URL and chatbot token server-side only.

    public function message(Request $request)
    {
        $nodeUrl = rtrim(config('chatbot.node_url'), '/') . '/api/chat';

        $payload = [
            'message' => $request->input('message', ''),
            'email'   => $request->input('email', ''),
            'history' => $request->input('history', []),
        ];

        if ($request->has('image')) {
            $payload['image'] = $request->input('image'); // { data, mediaType }
        }

        try {
            $response = Http::timeout(60)->post($nodeUrl, $payload);

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            // fall through to error response
        }

        return response()->json([
            'reply'    => "I'm having trouble connecting right now. Please try again in a moment.",
            'products' => [],
        ]);
    }

    // ── PRODUCTS ───────────────────────────────────────────────────────────────

    public function products()
    {
        $items = Item::with('category')->where('status', 1)->get();

        return response()->json($items->map(fn($item) => $this->mapProduct($item)));
    }

    public function categoryProducts($id)
    {
        $items = Item::with('category')
            ->where('status', 1)
            ->where('category_id', $id)
            ->get();

        return response()->json($items->map(fn($item) => $this->mapProduct($item)));
    }

    // ── CATEGORIES (mapped as Shopify-style collections) ──────────────────────

    public function categories()
    {
        $cats = Category::where('status', 1)->get();

        return response()->json($cats->map(fn($cat) => [
            'id'     => $cat->id,
            'title'  => $cat->name,
            'handle' => $cat->slug,
        ]));
    }

    // ── ORDERS ─────────────────────────────────────────────────────────────────

    public function ordersByEmail(Request $request)
    {
        $email = $request->query('email', '');
        $user  = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([]);
        }

        $orders = Order::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return response()->json($orders->map(fn($o) => $this->mapOrder($o)));
    }

    public function orderByNumber($number)
    {
        $number = ltrim((string) $number, '#');
        $order  = Order::find((int) $number);

        if (!$order) {
            return response()->json(null);
        }

        return response()->json($this->mapOrder($order));
    }

    public function ordersCount()
    {
        return response()->json(['count' => Order::count()]);
    }

    // ── CUSTOMERS ──────────────────────────────────────────────────────────────

    public function customerByEmail(Request $request)
    {
        $email = $request->query('email', '');
        $user  = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(null);
        }

        $orders     = Order::where('user_id', $user->id)->get();
        $totalSpent = $orders->sum(function ($o) {
            $cart = is_array($o->cart) ? $o->cart : json_decode($o->cart ?? '[]', true);
            return collect($cart ?? [])->sum(
                fn($item) => (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1)
            );
        });

        return response()->json([
            'first_name'     => $user->first_name,
            'last_name'      => $user->last_name,
            'email'          => $user->email,
            'phone'          => $user->phone ?? null,
            'orders_count'   => $orders->count(),
            'total_spent'    => number_format($totalSpent, 2),
            'verified_email' => $user->email_verify == 1,
            'created_at'     => $user->created_at,
            'tags'           => '',
        ]);
    }

    public function customersCount()
    {
        return response()->json(['count' => User::count()]);
    }

    // ── STORE STATS ────────────────────────────────────────────────────────────

    public function stats()
    {
        return response()->json([
            'total_products'  => Item::where('status', 1)->count(),
            'total_orders'    => Order::count(),
            'total_customers' => User::count(),
        ]);
    }

    // ── HELPERS ────────────────────────────────────────────────────────────────

    private function mapProduct(Item $item): array
    {
        $price    = ($item->discount_price && $item->discount_price > 0)
            ? $item->discount_price
            : ($item->previous_price ?? 0);
        $filename  = $item->photo ? basename($item->photo) : null;
        $localPath = $filename ? storage_path('app/public/images/' . $filename) : null;
        $imageUrl  = ($localPath && file_exists($localPath))
            ? url('storage/images/' . $filename)
            : url('storage/images/8VD5wedding-rings.png');

        return [
            'id'           => $item->id,
            'title'        => $item->name,
            'handle'       => $item->slug,
            'product_type' => $item->category->name ?? '',
            'tags'         => $item->tags ?? '',
            'body_html'    => $item->sort_details ?? '',
            'variants'     => [['price' => (string) $price]],
            'images'       => $imageUrl ? [['src' => $imageUrl]] : [],
        ];
    }

    private function mapOrder(Order $order): array
    {
        $cart         = is_array($order->cart) ? $order->cart : json_decode($order->cart ?? '[]', true);
        $shippingInfo = is_array($order->shipping_info)
            ? $order->shipping_info
            : json_decode($order->shipping_info ?? '{}', true);

        $lineItems = collect($cart ?? [])->map(fn($item) => [
            'quantity' => (int) ($item['quantity'] ?? 1),
            'title'    => $item['name'] ?? $item['title'] ?? 'Item',
        ])->toArray();

        $subtotal = collect($cart ?? [])->sum(
            fn($item) => (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1)
        );
        $total = $subtotal + (float) ($order->shipping ?? 0);

        $shippingAddress = null;
        if (!empty($shippingInfo)) {
            $parts = array_filter([
                $shippingInfo['address']  ?? $shippingInfo['address1'] ?? null,
                $shippingInfo['city']     ?? null,
                $shippingInfo['country']  ?? null,
            ]);
            $shippingAddress = implode(', ', $parts) ?: null;
        }

        return [
            'name'             => '#' . $order->id,
            'fulfillment_status' => $order->order_status  ?? 'processing',
            'financial_status'   => $order->payment_status ?? 'pending',
            'total_price'      => number_format($total, 2),
            'line_items'       => $lineItems,
            'tracking_url'     => null,
            'tracking_number'  => null,
            'created_at'       => $order->created_at,
            'shipping_address' => $shippingAddress,
        ];
    }
}