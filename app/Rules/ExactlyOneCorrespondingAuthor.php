<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExactlyOneCorrespondingAuthor implements ValidationRule
{
    
   /**
     * Run the validation rule.
     *
     * @param  \Closure(string, string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail('El formato de autores no es válido.');
            return;
        }

        $count = collect($value)
            ->filter(fn ($author) => isset($author['is_corresponding']) && $author['is_corresponding'])
            ->count();

        if ($count !== 1) {
            $fail('Debe haber exactamente un autor de correspondencia seleccionado.');
        }
    }
}
