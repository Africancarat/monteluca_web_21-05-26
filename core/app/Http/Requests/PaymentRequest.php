<?php

namespace App\Http\Requests;

use App\Helpers\PriceHelper;
use App\Models\ShippingService;
use App\Models\State;
use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    use Concerns\SanitizesInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->single_page_checkout == 1) {
            $this->trimStrings([
                'bill_first_name',
                'bill_last_name',
                'bill_phone',
                'bill_address1',
                'bill_city',
                'bill_zip',
            ]);
            $this->normalizeEmail('bill_email');
        }
    }

    public function rules(): array
    {
        if (PriceHelper::CheckDigital() == false) {
            return [];
        }

        $stateRequired = State::whereStatus(1)->count() !== 0;
        $shippingRequired = ShippingService::whereStatus(1)->count() === 0 || PriceHelper::CheckDigital() == true;

        $rules = [
            'state_id' => array_filter([
                $stateRequired ? 'required' : 'nullable',
                'integer',
                Rule::exists('states', 'id')->where(fn ($q) => $q->where('status', 1)),
            ]),
            'shipping_id' => array_filter([
                $shippingRequired ? 'required' : 'nullable',
                'integer',
                Rule::exists('shipping_services', 'id')->where(fn ($q) => $q->where('status', 1)),
            ]),
        ];

        if ($this->single_page_checkout == 1) {
            $rules = array_merge(
                $rules,
                ValidationRules::personName('bill_first_name'),
                ValidationRules::personName('bill_last_name'),
                ValidationRules::email('bill_email'),
                ValidationRules::phone('bill_phone'),
                ValidationRules::addressLine('bill_address1'),
                ValidationRules::zip('bill_zip'),
                ValidationRules::personName('bill_city'),
            );
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'state_id.required'   => __('Please select your shipping state.'),
            'shipping_id.required'   => __('Please select your shipping method.'),
            'bill_phone.digits' => __('Phone number must contain exactly 10 digits.'),
        ];
    }

}
