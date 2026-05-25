<?php

namespace App\Http\Requests\Concerns;

/**
 * Trims string input and normalizes common fields before validation runs.
 */
trait SanitizesInput
{
    /**
     * @param  list<string>  $keys
     */
    protected function trimStrings(array $keys): void
    {
        $merge = [];

        foreach ($keys as $key) {
            if (! $this->has($key)) {
                continue;
            }

            $value = $this->input($key);

            if (is_string($value)) {
                $merge[$key] = trim($value);
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    protected function normalizeEmail(string $key): void
    {
        if ($this->has($key) && is_string($this->input($key))) {
            $this->merge([$key => strtolower(trim($this->input($key)))]);
        }
    }

    /**
     * Strip HTML from a text field (contact messages, etc.).
     */
    protected function stripHtml(string $key): void
    {
        if ($this->has($key) && is_string($this->input($key))) {
            $this->merge([$key => strip_tags(trim($this->input($key)))]);
        }
    }
}
