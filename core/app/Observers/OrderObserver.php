<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\Referral\ReferralRewardService;

class OrderObserver
{
    public function created(Order $order): void
    {
        $service = app(ReferralRewardService::class);
        $service->attachOrderFromSession($order, $this->orderSubtotal($order));

        if ($service->isOrderCompleted($order)) {
            $service->processCompletedOrder($order);
        }
    }

    public function updated(Order $order): void
    {
        $service = app(ReferralRewardService::class);

        if ($order->wasChanged('order_status') || $order->wasChanged('payment_status')) {
            if ($service->isOrderCompleted($order)) {
                $service->processCompletedOrder($order);
            }
        }

        if ($order->wasChanged('order_status') && $order->order_status === 'Canceled') {
            $service->cancelForOrder($order);
        }
    }

    protected function orderSubtotal(Order $order): float
    {
        $cart = json_decode($order->cart, true) ?? [];
        $total = 0;

        foreach ($cart as $items) {
            $total += ((float) ($items['main_price'] ?? 0) + (float) ($items['attribute_price'] ?? 0))
                * (int) ($items['qty'] ?? 1);
        }

        return round($total, 2);
    }
}
