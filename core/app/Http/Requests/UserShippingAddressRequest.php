<?php

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserShippingAddressRequest extends FormRequest
{
    use Concerns\SanitizesInput;

    public const ALLOWED_KEYS = [
        'ship_address1',
        'ship_address2',
        'ship_zip',
        'ship_city',
        'ship_company',
        'ship_country',
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
            ValidationRules::addressLine('ship_address1'),
            ['ship_address2' => ['nullable', 'string', 'max:255']],
            ValidationRules::zip('ship_zip', false),
            ValidationRules::personName('ship_city'),
            ['ship_company' => ['nullable', 'string', 'max:150']],
            ValidationRules::country('ship_country'),
        );
    }
}
