<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users',
            'password'          => ['required', 'confirmed', Password::defaults()],
            'knowledge_area_id' => 'required|exists:knowledge_areas,id',
            'institution_id'    => 'required|exists:institutions,id'
        ];
    }

    public function attributes(): array
    {
        return [
            'name'              => 'nombre',
            'email'             => 'correo electrónico',
            'password'          => 'contraseña',
            'knowledge_area_id' => 'área de conocimiento',
            'institution_id'    => 'institución'
        ];
    }
}
