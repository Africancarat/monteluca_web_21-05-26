<?php

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutShippingRequest extends FormRequest
{
    use Concerns\SanitizesInput;

    public const SESSION_KEYS = [
        'ship_first_name',
        'ship_last_name',
        'ship_email',
        'ship_phone',
        'ship_company',
        'ship_address1',
        'ship_address2',
        'ship_zip',
        'ship_city',
        'ship_country',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->trimStrings([
            'ship_first_name',
            'ship_last_name',
            'ship_phone',
            'ship_company',
            'ship_address1',
            'ship_address2',
            'ship_zip',
            'ship_city',
            'ship_country',
        ]);
        $this->normalizeEmail('ship_email');
    }

    public function rules(): array
    {
        return array_merge(
            ValidationRules::personName('ship_first_name'),
            ValidationRules::personName('ship_last_name'),
            ValidationRules::email('ship_email'),
            ValidationRules::phone('ship_phone'),
            ['ship_company' => ['nullable', 'string', 'max:150']],
            ValidationRules::addressLine('ship_address1'),
            ['ship_address2' => ['nullable', 'string', 'max:255']],
            ValidationRules::zip('ship_zip'),
            ValidationRules::personName('ship_city'),
            ValidationRules::country('ship_country'),
        );
    }

    public function messages(): array
    {
        return [
            'ship_phone.digits' => __('Phone number must contain exactly 10 digits.'),
        ];
    }
}
