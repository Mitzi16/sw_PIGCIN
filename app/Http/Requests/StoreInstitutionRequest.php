<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstitutionRequest extends FormRequest
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
            'name'      => 'required|unique:institutions|max:255',
            'state_id'  => 'nullable|exists:states,id',
            'country_id'=> 'nullable|exists:countries,id',
            'status'    => 'required|boolean',
        ];
    }
    public function attributes(): array
    {
        return [
            'name'      => 'nombre',
            'state_id'  => 'Estado',
            'country_id'=> 'Pais',
            'status'    => 'estatus',
        ];
    }
}
