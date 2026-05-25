<?php

namespace App\Rules;

use App\Support\DisposableEmailChecker;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotDisposableEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        if (DisposableEmailChecker::isBlocked($value)) {
            $fail(__('Disposable or temporary email addresses are not allowed. Please use a permanent email.'));
        }
    }
}
