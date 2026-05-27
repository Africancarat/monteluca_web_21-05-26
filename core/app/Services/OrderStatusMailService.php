<?php

namespace App\Services;

use App\Helpers\EmailHelper;
use App\Helpers\PriceHelper;
use App\Jobs\EmailSendJob;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class OrderStatusMailService
{
    protected array $templateTypes = [
        'In Progress' => ['Order In Progress'],
        'Shipped' => ['Order Shipped'],
        'Delivered' => ['Order Delivered'],
        'Canceled' => ['Order Cancelled', 'Order Canceled'],
        'Cancelled' => ['Order Cancelled', 'Order Canceled'],
        'Refunded' => ['Order Refunded'],
    ];

    public function send(Order $order, string $status): bool
    {
        $templateType = $this->templateTypeForStatus($status);

        if (! $templateType) {
            return false;
        }

        $to = $this->recipientEmail($order);

        if (! $to) {
            Log::warning('Order status mail failed', [
                'order_id' => $order->id,
                'status' => $status,
                'reason' => 'missing_recipient',
            ]);

            return false;
        }

        $setting = Setting::first();

        $emailData = [
            'to' => $to,
            'type' => $templateType,
            'order_id' => $order->id,
            'user_name' => $this->customerName($order),
            'order_cost' => $this->orderCost($order),
            'transaction_number' => $order->transaction_number,
            'order_status' => $status,
            'site_title' => $setting ? $setting->title : config('app.name'),
            'send_admin_copy' => false,
        ];

        try {
            if ($setting && (int) $setting->is_queue_enabled === 1) {
                dispatch(new EmailSendJob($emailData, 'template'));

                Log::info('Order status mail sent', [
                    'order_id' => $order->id,
                    'status' => $status,
                    'template' => $templateType,
                    'to' => $to,
                    'queued' => true,
                ]);

                return true;
            }

            $sent = (new EmailHelper())->sendTemplateMail($emailData);

            if ($sent) {
                Log::info('Order status mail sent', [
                    'order_id' => $order->id,
                    'status' => $status,
                    'template' => $templateType,
                    'to' => $to,
                    'queued' => false,
                ]);

                return true;
            }

            Log::warning('Order status mail failed', [
                'order_id' => $order->id,
                'status' => $status,
                'template' => $templateType,
                'to' => $to,
            ]);
        } catch (\Throwable $e) {
            Log::error('Order status mail failed', [
                'order_id' => $order->id,
                'status' => $status,
                'template' => $templateType,
                'to' => $to,
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    protected function templateTypeForStatus(string $status): ?string
    {
        foreach ($this->templateTypes[$status] ?? [] as $type) {
            if (EmailTemplate::whereType($type)->exists()) {
                return $type;
            }
        }

        if (array_key_exists($status, $this->templateTypes)) {
            Log::warning('Order status mail failed', [
                'status' => $status,
                'reason' => 'missing_template',
                'expected_templates' => $this->templateTypes[$status],
            ]);
        }

        return null;
    }

    protected function recipientEmail(Order $order): ?string
    {
        $billingInfo = json_decode($order->billing_info, true) ?: [];

        return $billingInfo['bill_email'] ?? $order->user->email ?? null;
    }

    protected function customerName(Order $order): string
    {
        $billingInfo = json_decode($order->billing_info, true) ?: [];
        $billingName = trim(($billingInfo['bill_first_name'] ?? '').' '.($billingInfo['bill_last_name'] ?? ''));
        $userName = trim($order->user->displayName());

        return $billingName ?: ($userName ?: __('Customer'));
    }

    protected function orderCost(Order $order): string
    {
        $amount = PriceHelper::OrderTotal($order, 'trns');
        $setting = Setting::first();

        if ($setting && (int) $setting->currency_direction === 1) {
            return $order->currency_sign.$amount;
        }

        return $amount.$order->currency_sign;
    }
}
