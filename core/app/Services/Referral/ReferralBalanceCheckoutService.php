<?php

namespace App\Services\Referral;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ReferralBalanceCheckoutService
{
    /**
     * Amount of referral_balance that can be applied (full balance, capped by order total).
     */
    public function amountToApply(float $availableBalance, float $orderAmount): float
    {
        if ($orderAmount <= 0 || $availableBalance <= 0) {
            return 0;
        }

        return round(min($availableBalance, $orderAmount), 2);
    }

    public function apply(User $user, float $orderAmount): array
    {
        if ($orderAmount <= 0) {
            return [
                'success' => false,
                'message' => __('Unable to use referral balance. Insufficient balance.'),
            ];
        }

        return DB::transaction(function () use ($user, $orderAmount) {
            $this->restoreSessionDeduction($user);

            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->first();

            if (! $lockedUser) {
                return [
                    'success' => false,
                    'message' => __('Unable to use referral balance. Insufficient balance.'),
                ];
            }

            $available = (float) $lockedUser->referral_balance;
            $amountToUse = $this->amountToApply($available, $orderAmount);

            if ($amountToUse <= 0) {
                return [
                    'success' => false,
                    'message' => __('Unable to use referral balance. Insufficient balance.'),
                ];
            }

            $lockedUser->decrement('referral_balance', $amountToUse);

            $payable = round(max(0, $orderAmount - $amountToUse), 2);

            Session::put('referral_balance_applied', [
                'discount' => $amountToUse,
                'payable' => $payable,
                'order_amount' => $orderAmount,
            ]);

            return [
                'success' => true,
                'discount' => $amountToUse,
                'payable' => $payable,
                'message' => __('Referral balance applied successfully.'),
                'remaining_balance' => round($available - $amountToUse, 2),
            ];
        });
    }

    public function remove(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->restoreSessionDeduction($user);
            Session::forget('referral_balance_applied');
        });
    }

    public function clearApplied(?User $user): void
    {
        if ($user) {
            $this->remove($user);
        } else {
            Session::forget('referral_balance_applied');
        }
    }

    public function sessionDiscount(): float
    {
        $applied = Session::get('referral_balance_applied');

        return $applied ? (float) ($applied['discount'] ?? 0) : 0;
    }

    protected function restoreSessionDeduction(User $user): void
    {
        $applied = Session::get('referral_balance_applied');

        if (! $applied || empty($applied['discount'])) {
            return;
        }

        $amount = (float) $applied['discount'];

        User::query()->whereKey($user->id)->increment('referral_balance', $amount);

        Session::forget('referral_balance_applied');
    }
}
