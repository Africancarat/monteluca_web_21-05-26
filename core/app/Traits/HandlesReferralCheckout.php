<?php

namespace App\Traits;

use App\Helpers\CheckoutDiscountHelper;
use App\Models\Order;
use App\Services\Referral\ReferralRewardService;

trait HandlesReferralCheckout
{
    protected function orderDiscountFromSession(): array
    {
        return CheckoutDiscountHelper::orderDiscountPayload();
    }

    protected function applyCheckoutDiscounts(float $grandTotal): float
    {
        return CheckoutDiscountHelper::applyDiscountsToTotal($grandTotal);
    }

    protected function linkReferralToOrder(Order $order, float $orderSubtotal): void
    {
        app(ReferralRewardService::class)->attachOrderFromSession($order, $orderSubtotal);
    }

    /**
     * Clear checkout session after order placement (balance already deducted on apply).
     */
    protected function clearCheckoutSessions(): void
    {
        \Illuminate\Support\Facades\Session::forget(['cart', 'discount', 'coupon', 'referral', 'referral_balance_applied']);
    }
}
