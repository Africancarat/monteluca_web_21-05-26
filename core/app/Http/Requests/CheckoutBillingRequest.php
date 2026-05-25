<?php

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutBillingRequest extends FormRequest
{
    use Concerns\SanitizesInput;

    /** Keys allowed in session (prevents mass-assignment via extra POST fields). */
    public const SESSION_KEYS = [
        'bill_first_name',
        'bill_last_name',
        'bill_email',
        'bill_phone',
        'bill_company',
        'bill_address1',
        'bill_address2',
        'bill_zip',
        'bill_city',
        'bill_country',
        'same_ship_address',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->trimStrings([
            'bill_first_name',
            'bill_last_name',
            'bill_phone',
            'bill_company',
            'bill_address1',
            'bill_address2',
            'bill_zip',
            'bill_city',
            'bill_country',
        ]);
        $this->normalizeEmail('bill_email');
    }

    public function rules(): array
    {
        return array_merge(
            ValidationRules::personName('bill_first_name'),
            ValidationRules::personName('bill_last_name'),
            ValidationRules::email('bill_email'),
            ValidationRules::phone('bill_phone'),
            ['bill_company' => ['nullable', 'string', 'max:150']],
            ValidationRules::addressLine('bill_address1'),
            ['bill_address2' => ['nullable', 'string', 'max:255']],
            ValidationRules::zip('bill_zip'),
            ValidationRules::personName('bill_city'),
            ValidationRules::country('bill_country'),
            ['same_ship_address' => ['nullable', 'boolean']],
        );
    }

    public function messages(): array
    {
        return [
            'bill_phone.digits' => __('Phone number must contain exactly 10 digits.'),
        ];
    }
}
