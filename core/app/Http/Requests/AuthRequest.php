<?php

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    use Concerns\SanitizesInput;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize input before rules run (trim email, never validate raw spaces).
     */
    protected function prepareForValidation(): void
    {
        $this->normalizeEmail('login_email');
    }

    public function rules(): array
    {
        return array_merge(
            ValidationRules::loginEmail(),
            ValidationRules::password('login_password', 1, 255, true),
        );
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'login_email.required' => __('Email field is required.'),
            'login_email.email' => __('The email must be a valid email address.'),
            'login_email.max' => __('The email may not be greater than 255 characters.'),
            'login_password.required' => __('Password field is required.'),
            'login_password.max' => __('The password may not be greater than 255 characters.'),
        ];
    }

    /**
     * Friendly attribute names for default Laravel messages.
     */
    public function attributes(): array
    {
        return [
            'login_email' => __('email address'),
            'login_password' => __('password'),
        ];
    }
}
