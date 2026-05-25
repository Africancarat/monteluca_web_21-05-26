<?php

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProfileUpdateRequest extends FormRequest
{
    use Concerns\SanitizesInput;

    public function authorize(): bool
    {
        if ($this->filled('user_id') && ! Auth::guard('admin')->check()) {
            return false;
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->trimStrings(['first_name', 'last_name', 'phone']);
        $this->normalizeEmail('email');

        if (! Auth::guard('admin')->check()) {
            $this->request->remove('user_id');
        }
    }

    public function rules(): array
    {
        $userId = Auth::guard('web')->id();
        if (Auth::guard('admin')->check() && $this->filled('user_id')) {
            $userId = (int) $this->input('user_id');
        }

        return array_merge(
            ValidationRules::personName('first_name'),
            ValidationRules::personName('last_name'),
            ValidationRules::emailUnique('email', 'users', $userId, true),
            ValidationRules::phone('phone'),
            ['password' => ValidationRules::strongPassword(false)],
            ['password_confirmation' => ['nullable', 'required_with:password', 'same:password']],
            ValidationRules::image('photo', false, 2048),
            ['newsletter' => ['nullable', 'boolean']],
            ['user_id' => ['nullable', 'integer', 'exists:users,id']],
        );
    }

    public function messages(): array
    {
        return [
            'phone.digits' => __('Phone number must contain exactly 10 digits.'),
            'password.mixed' => __('Password must contain uppercase and lowercase letters.'),
            'password.numbers' => __('Password must contain at least one number.'),
            'password.symbols' => __('Password must contain at least one symbol.'),
            'password.uncompromised' => __('This password has appeared in a data breach. Please choose a different password.'),
            'password_confirmation.same' => __('Password confirmation does not match.'),
        ];
    }
}
