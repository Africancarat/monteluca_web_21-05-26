<?php

namespace App\Services\Referral;

use App\Models\ReferralCode;
use App\Models\ReferralTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ReferralApplyService
{
    public function validateAndCalculate(string $referralCode, float $orderAmount): array
    {
        $code = ReferralCode::query()
            ->where('referral_code', $referralCode)
            ->where('status', ReferralCode::STATUS_ACTIVE)
            ->first();

        if (! $code) {
            return [
                'success' => false,
                'message' => __('Invalid referral code'),
            ];
        }

        $discount = round($orderAmount * ((float) $code->discount_percent / 100), 2);
        $finalAmount = round(max(0, $orderAmount - $discount), 2);

        return [
            'success' => true,
            'discount' => $discount,
            'final_amount' => $finalAmount,
            'message' => __('Referral applied'),
            'code' => $code,
        ];
    }

    public function applyToSession(string $referralCode, float $orderAmount): array
    {
        $result = $this->validateAndCalculate($referralCode, $orderAmount);

        if (! $result['success']) {
            return $result;
        }

        /** @var ReferralCode $code */
        $code = $result['code'];

        return DB::transaction(function () use ($code, $orderAmount, $result) {
            $this->cancelSessionPendingTransaction();

            $transaction = ReferralTransaction::create([
                'referral_code_id' => $code->id,
                'referrer_user_id' => $code->user_id,
                'order_amount' => $orderAmount,
                'discount_amount' => $result['discount'],
                'cashback_amount' => round($orderAmount * ((float) $code->cashback_percent / 100), 2),
                'status' => ReferralTransaction::STATUS_PENDING,
            ]);

            Session::put('referral', [
                'discount' => $result['discount'],
                'final_amount' => $result['final_amount'],
                'transaction_id' => $transaction->id,
                'code' => [
                    'id' => $code->id,
                    'referral_code' => $code->referral_code,
                    'discount_percent' => $code->discount_percent,
                    'cashback_percent' => $code->cashback_percent,
                    'user_id' => $code->user_id,
                ],
            ]);

            return [
                'success' => true,
                'discount' => $result['discount'],
                'final_amount' => $result['final_amount'],
                'message' => $result['message'],
            ];
        });
    }

    public function clearSession(): void
    {
        $this->cancelSessionPendingTransaction();
        Session::forget('referral');
    }

    protected function cancelSessionPendingTransaction(): void
    {
        $referral = Session::get('referral');

        if (! $referral || empty($referral['transaction_id'])) {
            return;
        }

        ReferralTransaction::query()
            ->where('id', $referral['transaction_id'])
            ->where('status', ReferralTransaction::STATUS_PENDING)
            ->whereNull('order_id')
            ->update(['status' => ReferralTransaction::STATUS_CANCELLED]);
    }
}
