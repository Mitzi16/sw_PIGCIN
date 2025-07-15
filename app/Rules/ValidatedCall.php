<?php

namespace App\Rules;

use App\Models\Call;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class ValidatedCall implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail('Selecciona una convocatoria válida');
        }
    }

    public function passes($attribute, $value)
    {
        // Verifica si existe un registro que cumple con las condiciones
        return Call::where('id', $value)
            ->where('status', true)
            // ->where('institution_id', User::find(Auth::id())->institution_id)
            ->exists();
    }
}
