<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class CheckoutDiscountHelper
{
    public static function couponDiscount(): float
    {
        $coupon = Session::get('coupon');

        return $coupon ? (float) ($coupon['discount'] ?? 0) : 0;
    }

    public static function referralCodeDiscount(): float
    {
        $referral = Session::get('referral');

        return $referral ? (float) ($referral['discount'] ?? 0) : 0;
    }

    /** @deprecated Use referralCodeDiscount() */
    public static function referralDiscount(): float
    {
        return self::referralCodeDiscount();
    }

    public static function referralBalanceDiscount(): float
    {
        $applied = Session::get('referral_balance_applied');

        return $applied ? (float) ($applied['discount'] ?? 0) : 0;
    }

    public static function totalPromoDiscount(): float
    {
        return self::couponDiscount()
            + self::referralCodeDiscount()
            + self::referralBalanceDiscount();
    }

    /**
     * Build discount payload stored on orders (coupon + referral code + balance).
     */
    public static function orderDiscountPayload(): array
    {
        $coupon = Session::get('coupon');
        $referral = Session::get('referral');
        $balance = Session::get('referral_balance_applied');

        $totalDiscount = self::totalPromoDiscount();

        if ($totalDiscount <= 0) {
            return [];
        }

        return [
            'discount' => $totalDiscount,
            'code' => $coupon['code'] ?? null,
            'referral' => $referral['code'] ?? null,
            'referral_transaction_id' => $referral['transaction_id'] ?? null,
            'referral_balance' => $balance ? [
                'amount' => (float) ($balance['discount'] ?? 0),
            ] : null,
        ];
    }

    public static function applyDiscountsToTotal(float $grandTotal): float
    {
        return max(0, $grandTotal - self::totalPromoDiscount());
    }

    public static function hasAnyDiscount(): bool
    {
        return self::totalPromoDiscount() > 0;
    }
}
