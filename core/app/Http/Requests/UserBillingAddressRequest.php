<?php

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserBillingAddressRequest extends FormRequest
{
    use Concerns\SanitizesInput;

    public const ALLOWED_KEYS = [
        'bill_address1',
        'bill_address2',
        'bill_zip',
        'bill_city',
        'bill_company',
        'bill_country',
    ];

    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation(): void
    {
        $this->trimStrings(self::ALLOWED_KEYS);
    }

    public function rules(): array
    {
        return array_merge(
            ValidationRules::addressLine('bill_address1'),
            ['bill_address2' => ['nullable', 'string', 'max:255']],
            ValidationRules::zip('bill_zip', false),
            ValidationRules::personName('bill_city'),
            ['bill_company' => ['nullable', 'string', 'max:150']],
            ValidationRules::country('bill_country'),
        );
    }
}
