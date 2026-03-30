<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $password = (string) $value;

        $valida = strlen($password) >= 8
            && preg_match('/[a-z]/', $password)
            && preg_match('/[A-Z]/', $password)
            && preg_match('/\d/', $password)
            && preg_match('/[^a-zA-Z\d]/', $password);

        if (!$valida) {
            $fail('A senha deve ter pelo menos 8 caracteres, com letra maiuscula, letra minuscula, numero e caractere especial.');
        }
    }
}
