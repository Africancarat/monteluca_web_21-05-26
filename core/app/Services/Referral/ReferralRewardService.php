<?php

namespace App\Services\Referral;

use App\Models\Order;
use App\Models\ReferralCode;
use App\Models\ReferralTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReferralRewardService
{
    public function attachOrderFromSession(Order $order, float $orderAmount): void
    {
        $referral = session('referral');

        if (! $referral || empty($referral['transaction_id'])) {
            return;
        }

        DB::transaction(function () use ($order, $orderAmount, $referral) {
            $transaction = ReferralTransaction::query()
                ->where('id', $referral['transaction_id'])
                ->lockForUpdate()
                ->first();

            if (! $transaction || $transaction->status !== ReferralTransaction::STATUS_PENDING) {
                return;
            }

            $transaction->update([
                'order_id' => $order->id,
                'customer_id' => $order->user_id ?: null,
                'order_amount' => $orderAmount,
                'discount_amount' => $referral['discount'] ?? $transaction->discount_amount,
            ]);
        });
    }

    /**
     * Reward referrer when order is completed (Delivered + Paid).
     */
    public function processCompletedOrder(Order $order): void
    {
        if (! $this->isOrderCompleted($order)) {
            return;
        }

        DB::transaction(function () use ($order) {
            $transaction = ReferralTransaction::query()
                ->where('order_id', $order->id)
                ->whereIn('status', [
                    ReferralTransaction::STATUS_PENDING,
                    ReferralTransaction::STATUS_COMPLETED,
                ])
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                return;
            }

            if ($transaction->status === ReferralTransaction::STATUS_REWARDED) {
                return;
            }

            if ($transaction->status === ReferralTransaction::STATUS_PENDING) {
                $transaction->update(['status' => ReferralTransaction::STATUS_COMPLETED]);
                $transaction->refresh();
            }

            $this->rewardReferrer($transaction);
        });
    }

    /** @deprecated Use processCompletedOrder() */
    public function processPaidOrder(Order $order): void
    {
        $this->processCompletedOrder($order);
    }

    public function cancelForOrder(Order $order): void
    {
        ReferralTransaction::query()
            ->where('order_id', $order->id)
            ->whereIn('status', [
                ReferralTransaction::STATUS_PENDING,
                ReferralTransaction::STATUS_COMPLETED,
            ])
            ->update(['status' => ReferralTransaction::STATUS_CANCELLED]);
    }

    public function isOrderCompleted(Order $order): bool
    {
        return $order->order_status === 'Delivered'
            && $order->payment_status === 'Paid';
    }

    protected function rewardReferrer(ReferralTransaction $transaction): void
    {
        if ($transaction->status === ReferralTransaction::STATUS_REWARDED) {
            return;
        }

        $alreadyRewarded = ReferralTransaction::query()
            ->where('order_id', $transaction->order_id)
            ->where('status', ReferralTransaction::STATUS_REWARDED)
            ->where('id', '!=', $transaction->id)
            ->exists();

        if ($alreadyRewarded) {
            $transaction->update(['status' => ReferralTransaction::STATUS_CANCELLED]);

            return;
        }

        $referralCode = ReferralCode::query()
            ->where('id', $transaction->referral_code_id)
            ->lockForUpdate()
            ->first();

        if (! $referralCode) {
            return;
        }

        $referrer = User::query()->lockForUpdate()->find($transaction->referrer_user_id);

        if (! $referrer) {
            return;
        }

        $orderAmount = (float) $transaction->order_amount;
        $cashback = round($orderAmount * ((float) $referralCode->cashback_percent / 100), 2);

        $referrer->increment('referral_balance', $cashback);

        $transaction->update([
            'status' => ReferralTransaction::STATUS_REWARDED,
            'cashback_amount' => $cashback,
        ]);

        $referralCode->increment('total_usage');
        $referralCode->increment('total_earned_cashback', $cashback);
    }
}
