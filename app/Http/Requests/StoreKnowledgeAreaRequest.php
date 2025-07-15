<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKnowledgeAreaRequest extends FormRequest
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
            'name'        => 'required|unique:knowledge_areas|max:2555',
            'description' => 'required|max:10000',
            'parent_id' => 'nullable|exists:knowledge_areas,id'
        ];
    }
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'Descripción',
            'parent_id' => 'área de conocimiento'
        ];
    }
}
