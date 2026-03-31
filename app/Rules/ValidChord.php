<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidChord implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $chord = trim((string) $value);

        if ($chord === '') {
            return;
        }

        if (preg_match('/^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\+|-|[0-9#b])|\([^)\]]+\))*(?:\/[A-G](?:#|b)?)?$/', $chord) === 1) {
            return;
        }

        $fail('Informe um tom valido, como G, Dm, F#m, Bb ou C/E.');
    }
}
