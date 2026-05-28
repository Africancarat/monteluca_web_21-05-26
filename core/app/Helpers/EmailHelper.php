<?php



namespace App\Helpers;



use App\Models\EmailTemplate;

use App\Models\Order;

use App\Models\Setting;

use App\Helpers\GstHelper;

use App\Support\MailConfigurator;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Session;

use PHPMailer\PHPMailer\Exception;

use PHPMailer\PHPMailer\PHPMailer;



class EmailHelper

{

    public PHPMailer $mail;



    public ?Setting $setting;



    public function __construct()

    {

        $this->setting = Setting::first();

        $this->mail = new PHPMailer(true);

        MailConfigurator::applyTo($this->mail, $this->setting);

    }



    /**

     * @return bool True when the message was accepted for delivery.

     */

    public function sendTemplateMail(array $emailData): bool

    {

        $template = EmailTemplate::whereType($emailData['type'])->first();



        if (! $template || ! $this->setting) {

            Log::warning('Email template or settings missing', ['type' => $emailData['type'] ?? null]);



            return false;

        }



        try {

            $email_body = $this->replaceTemplateVariables($template->body, $emailData);

            $email_subject = $this->replaceTemplateVariables($template->subject, $emailData);



            $this->prepareMessage(

                $emailData['to'],

                $email_subject,

                $email_body

            );



            $this->mail->send();



            if (($emailData['send_admin_copy'] ?? true) && $this->setting->order_mail == 1) {

                $this->adminMail($emailData);

            }



            return true;

        } catch (Exception $e) {

            Log::error('Template mail failed', [

                'type' => $emailData['type'] ?? null,

                'to' => $this->maskEmail($emailData['to'] ?? ''),

                'message' => $e->getMessage(),

            ]);



            return false;

        } finally {

            $this->resetMailer();

        }

    }



    /**

     * @return bool True when the message was accepted for delivery.

     */

    public function sendCustomMail(array $emailData): bool

    {

        if (empty($emailData['to']) || empty($emailData['subject'])) {

            return false;

        }



        try {

            $this->prepareMessage(

                $emailData['to'],

                $emailData['subject'],

                $emailData['body'] ?? ''

            );



            $this->mail->send();



            return true;

        } catch (Exception $e) {

            Log::error('Custom mail failed', [

                'to' => $this->maskEmail($emailData['to']),

                'message' => $e->getMessage(),

            ]);



            return false;

        } finally {

            $this->resetMailer();

        }

    }



    public static function getEmail()

    {

        $user = Auth::user();

        if (isset($user)) {

            return $user->email;

        }



        return Session::get('billing_address')['bill_email'] ?? null;

    }



    public function adminMail(array $emailData): bool

    {

        $template = EmailTemplate::whereType('New Order Admin')->first();



        if (! $template || ! $this->setting || empty($this->setting->contact_email)) {

            return false;

        }



        try {

            $email_body = $this->replaceTemplateVariables($template->body, $emailData);

            $email_subject = $this->replaceTemplateVariables($template->subject, $emailData);



            $this->mail->clearAddresses();

            $this->prepareMessage(

                $this->setting->contact_email,

                $email_subject,

                $email_body

            );



            $this->mail->send();



            return true;

        } catch (\Throwable $e) {

            Log::error('Admin mail failed', ['message' => $e->getMessage()]);



            return false;

        } finally {

            $this->resetMailer();

        }

    }



    protected function prepareMessage(string $to, string $subject, string $body): void

    {

        if (! $this->setting) {

            throw new Exception('Mail settings are not configured.');

        }



        $this->mail->setFrom(

            $this->setting->email_from,

            $this->setting->email_from_name

        );

        $this->mail->addAddress($to);

        $this->mail->isHTML(true);

        $this->mail->Subject = $subject;

        $this->mail->Body = $body;

    }

    protected function replaceTemplateVariables(string $body, array $emailData): string

    {
        $order = $this->orderFromEmailData($emailData);

        $replacements = [

            '{user_name}' => $emailData['user_name'] ?? '',

            '{order_cost}' => $emailData['order_cost'] ?? '',

            '{transaction_number}' => $emailData['transaction_number'] ?? '',

            '{order_status}' => $emailData['order_status'] ?? '',

            '{site_title}' => $emailData['site_title'] ?? ($this->setting->title ?? ''),

        ];

        if ($order) {

            $replacements = array_merge($replacements, $this->orderTemplateVariables($order));

        }



        return strtr($body, $replacements);

    }

    protected function orderFromEmailData(array $emailData): ?Order

    {

        if (! empty($emailData['order_id'])) {

            return Order::find($emailData['order_id']);

        }

        if (! empty($emailData['transaction_number'])) {

            return Order::where('transaction_number', $emailData['transaction_number'])->first();

        }

        return null;

    }

    protected function orderTemplateVariables(Order $order): array

    {

        $cart = json_decode($order->cart, true) ?: [];

        $shipping = json_decode($order->shipping, true) ?: [];

        $discount = json_decode($order->discount, true) ?: [];

        $shippingInfo = json_decode($order->shipping_info, true) ?: [];

        $subtotal = $this->cartSubtotal($cart);

        $shippingCost = (float) ($shipping['price'] ?? 0);

        $discountAmount = (float) ($discount['discount'] ?? 0);

        $tax = (float) ($order->tax ?? 0);

        $gst = GstHelper::splitForSubtotal($subtotal);

        $cgstAmount = (float) ($gst['cgst_amount'] ?? 0);

        $sgstAmount = (float) ($gst['sgst_amount'] ?? 0);

        $totalGst = (float) ($gst['total_tax'] ?? 0);

        if ($totalGst <= 0 && $tax > 0) {

            $cgstAmount = round($tax / 2, 2);

            $sgstAmount = round($tax / 2, 2);

            $totalGst = $tax;

        }

        return [

            '{product_list}' => $this->productListHtml($order, $cart),

            '{shipping_address}' => $this->formatAddress($shippingInfo, 'ship'),

            '{payment_method}' => $order->payment_method ?? '',

            '{order_status}' => $order->order_status ?? '',

            '{subtotal}' => $this->formatBaseMoney($order, $subtotal),

            '{discount}' => $this->formatBaseMoney($order, $discountAmount),

            '{tax}' => $this->formatBaseMoney($order, $totalGst > 0 ? $totalGst : $tax),

            '{cgst}' => $this->formatBaseMoney($order, $cgstAmount),

            '{sgst}' => $this->formatBaseMoney($order, $sgstAmount),

            '{cgst_percent}' => (string) ($gst['cgst_percent'] ?? 0),

            '{sgst_percent}' => (string) ($gst['sgst_percent'] ?? 0),

            '{shipping_cost}' => $this->formatBaseMoney($order, $shippingCost),

            '{grand_total}' => $this->formatConvertedMoney($order, PriceHelper::OrderTotal($order, 'trns')),

            '{metal_type}' => $this->cartValueList($cart, ['metal_type', 'pdp_metal_type']),

            '{diamond_shape}' => $this->cartValueList($cart, ['selected_shape']),

            '{carat_weight}' => $this->cartValueList($cart, ['selected_carat', 'carat_weight']),

            '{clarity_grade}' => $this->cartValueList($cart, ['clarity_grade', 'pdp_diamond_clarity']),

            '{color_grade}' => $this->cartValueList($cart, ['color_grade', 'pdp_diamond_color']),

            '{estimated_delivery}' => $this->estimatedDelivery($order),

            '{order_date}' => optional($order->created_at)->format('M d, Y') ?? '',

            '{site_url}' => config('app.url'),

        ];

    }

    protected function productListHtml(Order $order, array $cart): string

    {

        if (empty($cart)) {

            return '';

        }

        $rows = '';

        foreach ($cart as $item) {

            $qty = (int) ($item['qty'] ?? 1);

            $unit = (float) ($item['main_price'] ?? 0) + (float) ($item['attribute_price'] ?? 0);

            $lineTotal = $unit * $qty;

            $details = $this->productDetails($item);

            $rows .= '<tr>';

            $rows .= '<td style="padding:8px;border:1px solid #ddd;">'.e($item['name'] ?? '').$details.'</td>';

            $rows .= '<td style="padding:8px;border:1px solid #ddd;text-align:center;">'.$qty.'</td>';

            $rows .= '<td style="padding:8px;border:1px solid #ddd;text-align:right;">'.$this->formatBaseMoney($order, $unit).'</td>';

            $rows .= '<td style="padding:8px;border:1px solid #ddd;text-align:right;">'.$this->formatBaseMoney($order, $lineTotal).'</td>';

            $rows .= '</tr>';

        }

        return '<table style="width:100%;border-collapse:collapse;">'
            .'<thead><tr>'
            .'<th style="padding:8px;border:1px solid #ddd;text-align:left;">Product</th>'
            .'<th style="padding:8px;border:1px solid #ddd;text-align:center;">Qty</th>'
            .'<th style="padding:8px;border:1px solid #ddd;text-align:right;">Price</th>'
            .'<th style="padding:8px;border:1px solid #ddd;text-align:right;">Total</th>'
            .'</tr></thead><tbody>'.$rows.'</tbody></table>';

    }

    protected function productDetails(array $item): string

    {

        $details = [];

        $map = [

            'Metal' => $this->firstCartValue($item, ['metal_type', 'pdp_metal_type']),

            'Shape' => $this->firstCartValue($item, ['selected_shape']),

            'Carat' => $this->firstCartValue($item, ['selected_carat', 'carat_weight']),

            'Clarity' => $this->firstCartValue($item, ['clarity_grade', 'pdp_diamond_clarity']),

            'Color' => $this->firstCartValue($item, ['color_grade', 'pdp_diamond_color']),

        ];

        foreach ($map as $label => $value) {

            if ($value !== '') {

                $details[] = e($label.': '.$value);

            }

        }

        return $details ? '<br><small>'.implode('<br>', $details).'</small>' : '';

    }

    protected function cartSubtotal(array $cart): float

    {

        $subtotal = 0;

        foreach ($cart as $item) {

            $subtotal += ((float) ($item['main_price'] ?? 0) + (float) ($item['attribute_price'] ?? 0)) * (int) ($item['qty'] ?? 1);

        }

        return $subtotal;

    }

    protected function cartValueList(array $cart, array $keys): string

    {

        $values = [];

        foreach ($cart as $item) {

            $value = $this->firstCartValue($item, $keys);

            if ($value !== '') {

                $values[] = $value;

            }

        }

        return implode(', ', array_values(array_unique($values)));

    }

    protected function firstCartValue(array $item, array $keys): string

    {

        foreach ($keys as $key) {

            if (! empty($item[$key])) {

                return is_array($item[$key]) ? implode(', ', $item[$key]) : (string) $item[$key];

            }

        }

        return '';

    }

    protected function formatAddress(array $address, string $prefix): string

    {

        if (empty($address)) {

            return '';

        }

        $parts = array_filter([

            trim(($address[$prefix.'_first_name'] ?? '').' '.($address[$prefix.'_last_name'] ?? '')),

            $address[$prefix.'_address1'] ?? null,

            $address[$prefix.'_address2'] ?? null,

            $address[$prefix.'_city'] ?? null,

            $address[$prefix.'_zip'] ?? null,

            $address[$prefix.'_country'] ?? null,

            $address[$prefix.'_phone'] ?? null,

            $address[$prefix.'_email'] ?? null,

        ]);

        return implode('<br>', array_map(fn ($part) => e($part), $parts));

    }

    protected function estimatedDelivery(Order $order): string

    {

        $meta = is_array($order->checkout_meta) ? $order->checkout_meta : [];

        return (string) (data_get($meta, 'estimated_delivery')
            ?? data_get($meta, 'estimated_delivery_date')
            ?? data_get($meta, 'delivery_date')
            ?? '');

    }

    protected function formatBaseMoney(Order $order, float $amount): string

    {

        return $this->formatConvertedMoney($order, round($amount * (float) $order->currency_value, 2));

    }

    protected function formatConvertedMoney(Order $order, float $amount): string

    {

        if ($this->setting && (int) $this->setting->currency_direction === 1) {

            return $order->currency_sign.$amount;

        }

        return $amount.$order->currency_sign;

    }



    protected function resetMailer(): void

    {

        $this->mail->clearAddresses();

        $this->mail->clearAttachments();

        $this->mail = new PHPMailer(true);

        MailConfigurator::applyTo($this->mail, $this->setting);

    }



    protected function maskEmail(string $email): string

    {

        if (! str_contains($email, '@')) {

            return '***';

        }



        [$local, $domain] = explode('@', $email, 2);



        return substr($local, 0, 1).'***@'.$domain;

    }

}


