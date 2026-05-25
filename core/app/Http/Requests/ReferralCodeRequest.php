<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferralCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $referralCode = $this->route('referral_code');
        $id = $referralCode ? ','.$referralCode->id : '';

        return [
            'user_id' => 'required|exists:users,id',
            'referral_code' => 'required|string|max:50|unique:referral_codes,referral_code'.$id,
            'discount_percent' => 'required|numeric|min:0|max:100',
            'cashback_percent' => 'required|numeric|min:0|max:100',
            'status' => ['required', Rule::in([0, 1])],
        ];
    }
}
