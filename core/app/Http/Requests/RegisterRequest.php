<?php

namespace App\Http\Requests;

use App\Models\Setting;
use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
    }

    public function rules(): array
    {
        $setting = Setting::first();

        return array_merge(
            ValidationRules::honeypot(),
            ValidationRules::personName('first_name'),
            ValidationRules::personName('last_name'),
            ValidationRules::emailUnique('email', 'users', null, true),
            ValidationRules::phoneUnique('phone', 'users'),
            ['password' => ValidationRules::strongPassword()],
            ['password_confirmation' => ['required', 'string', 'same:password']],
            ValidationRules::recaptcha((bool) ($setting->recaptcha ?? 0))
        );
    }

    public function messages(): array
    {
        return [
            'first_name.required' => __('First Name is required.'),
            'last_name.required' => __('Last Name field is required.'),
            'phone.required' => __('Phone Number is required.'),
            'phone.digits' => __('Phone number must contain exactly 10 digits.'),
            'phone.unique' => __('This phone number already exists.'),

            'email.required' => __('Email field is required.'),
            'email.email' => __('Please enter a valid email address.'),
            'email.unique' => __('This email has already been taken.'),
            'password.required' => __('Password field is required.'),
            'password.min' => __('Password must be at least 8 characters.'),
            'password.mixed' => __('Password must contain uppercase and lowercase letters.'),
            'password.numbers' => __('Password must contain at least one number.'),
            'password.symbols' => __('Password must contain at least one symbol.'),
        
            'password_confirmation.same' => __('Password confirmation does not match.'),
            'g-recaptcha-response.required' => __('Please verify that you are not a robot.'),
            'honeypot.max' => __('Please verify that you are not a robot.'),
        ];
    }
}
