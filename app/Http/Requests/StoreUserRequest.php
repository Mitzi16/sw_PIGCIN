<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'              => 'required|max:255',
            'email'             => 'required|email|max:255|unique:users',
            'password'          => 'required|max:20',
            'roles'             => 'nullable|array',
            'knowledge_area_id' => 'nullable|exists:knowledge_areas,id',
            'institution_id'    => 'nullable|exists:institutions,id',
        ];
    }
    public function attributes(): array
    {
        return [
            'name'              => 'nombre',
            'email'             => 'correo electrónico',
            'password'          => 'contraseña',
            'roles.*'           => 'roles',
            'knowledge_area_id' => 'area',
            'institution_id'    => 'institución',
        ];
    }
}
