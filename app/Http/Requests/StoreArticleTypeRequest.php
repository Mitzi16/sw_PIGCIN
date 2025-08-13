<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleTypeRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:article_types,name',
            'rubrics' => 'nullable|array',
            'rubrics.*.title' => 'required|string|max:255',
            'rubrics.*.items' => 'nullable|array',
            'rubrics.*.items.*.question' => 'required|string',
            'rubrics.*.items.*.answers' => 'nullable|array',
            'rubrics.*.items.*.answers.*.text' => 'required|string',
            'rubrics.*.items.*.answers.*.score' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del tipo de artículo es obligatorio.',
            'name.unique' => 'Ya existe un tipo de artículo con este nombre.',
            'rubrics.*.title.required' => 'El título de la rúbrica es obligatorio.',
            'rubrics.*.items.*.question.required' => 'La pregunta es obligatoria.',
            'rubrics.*.items.*.answers.*.text.required' => 'El texto de la respuesta es obligatorio.',
            'rubrics.*.items.*.answers.*.score.required' => 'El puntaje es obligatorio.',
            'rubrics.*.items.*.answers.*.score.numeric' => 'El puntaje debe ser un número.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
        ];
    }
}
