<?php

namespace App\Services;

use App\Helpers\PriceHelper;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OrderInventoryService
{
    public function __construct(
        protected InventoryReservationService $reservations
    ) {
    }

    /**
     * After successful payment: complete soft locks, then decrement physical stock.
     */
    public function finalizeSuccessfulCheckout(?string $sessionId = null): void
    {
        DB::transaction(function () use ($sessionId) {
            $this->reservations->completeSession($sessionId);
            PriceHelper::stockDecrese();
        });
    }

    /**
     * Payment failed or checkout abandoned before payment.
     */
    public function releaseCheckoutHold(?string $sessionId = null): void
    {
        $this->reservations->releaseSession($sessionId);
    }

    /**
     * Payment methods that decrement physical stock when the order is placed (often still Unpaid).
     *
     * @return list<string>
     */
    public function paymentMethodsThatDecrementStockOnPlacement(): array
    {
        return [
            'Cash On Delivery',
            'Bank Transfer',
        ];
    }

    /**
     * Whether this order should return stock when cancelled / unpaid after a prior decrement.
     */
    public function shouldRestockOrder(Order $order, ?string $paymentStatusBeforeChange = null): bool
    {
        $status = $paymentStatusBeforeChange ?? $order->payment_status;

        if ($status === 'Paid') {
            return true;
        }

        return in_array(
            (string) $order->payment_method,
            $this->paymentMethodsThatDecrementStockOnPlacement(),
            true
        );
    }

    /**
     * Order cancelled / refund approved — restore physical stock when it was previously reduced.
     */
    public function restockOrderIfDecremented(Order $order, ?string $paymentStatusBeforeChange = null): void
    {
        if (! $this->shouldRestockOrder($order, $paymentStatusBeforeChange)) {
            return;
        }

        $this->restockOrder($order);
    }

    /**
     * Order cancelled / refund approved — restore physical stock.
     */
    public function restockOrder(Order $order): void
    {
        $cart = json_decode($order->cart, true);
        if (! is_array($cart)) {
            return;
        }

        DB::transaction(function () use ($cart) {
            PriceHelper::restock($cart);
        });
    }
}
