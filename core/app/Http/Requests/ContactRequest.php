<?php

namespace App\Http\Requests;

use App\Models\Setting;
use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    use Concerns\SanitizesInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->trimStrings(['first_name', 'last_name', 'phone', 'honeypot']);
        $this->normalizeEmail('email');
        $this->stripHtml('message');
    }

    public function rules(): array
    {
        $setting = Setting::first();

        return array_merge(
            ValidationRules::honeypot(),
            ValidationRules::personName('first_name'),
            ValidationRules::personName('last_name'),
            ValidationRules::email('email'),
            ValidationRules::phone('phone'),
            ValidationRules::message('message', 2000),
            ValidationRules::recaptcha((bool) ($setting->recaptcha ?? 0))
        );
    }

    public function messages(): array
    {
        return [
            'first_name.required' => __('First name is required.'),
            'last_name.required' => __('Last name is required.'),
            'email.required' => __('Email field is required.'),
            'email.email' => __('Please enter a valid email address.'),
            'phone.required' => __('Phone number is required.'),
            'phone.digits' => __('Phone number must contain exactly 10 digits.'),
            'message.required' => __('Message is required.'),
            'g-recaptcha-response.required' => __('Please verify that you are not a robot.'),
            'honeypot.max' => __('Please verify that you are not a robot.'),
        ];
    }
}
