<?php

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AdminPasswordUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        return array_merge(
            ['current_password' => ['required', 'string', 'max:255']],
            ['new_password' => ValidationRules::strongPassword()],
            ['renew_password' => ['required', 'string', 'same:new_password']],
        );
    }

    public function messages(): array
    {
        return [
            'new_password.mixed' => __('Password must contain uppercase and lowercase letters.'),
            'new_password.numbers' => __('Password must contain at least one number.'),
            'new_password.symbols' => __('Password must contain at least one symbol.'),
            'new_password.uncompromised' => __('This password has appeared in a data breach. Please choose a different password.'),
            'renew_password.same' => __('Password confirmation does not match.'),
        ];
    }
}
