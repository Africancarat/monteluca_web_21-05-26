<?php

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class ResendVerificationRequest extends FormRequest
{
    use Concerns\SanitizesInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeEmail('email');
    }

    public function rules(): array
    {
        return ValidationRules::email('email', true, false);
    }
}
