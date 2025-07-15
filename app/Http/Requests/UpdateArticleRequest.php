<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
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
            'title'                     => 'required|string|max:2555|unique:articles,title,' . $this->id,
            'abstract'                  => 'required|max:10000',
            'key_works'                 => 'required|max:255',
            'knowledge_area_id'         => 'required|exists:knowledge_areas,id',
            'article_status_id'         => 'required|exists:article_statuses,id',
            'file'                      => 'nullable|mimes:pdf',

            'authors.*.prefix'          => 'required|max:255',
            'authors.*.name'            => 'required|max:255',
            'authors.*.surname'         => 'required|max:255',
            'authors.*.institution_id'  => 'required|exists:institutions,id',
            'authors.*.email'           => 'required|email|max:255',
            'article_type_id' => 'required|exists:article_types,id',

        ];
    }
    public function attributes(): array
    {
        return [
            'title'                     => 'titulo',
            'abstract'                  => 'resumén',
            'key_works'                 => 'palabras clave',
            'knowledge_area_id'         => 'area de conocimiento',
            'file'                      => 'archivo',
            'article_type_id' => 'tipo de artículo',

            'authors.*.prefix'          => 'prefijo',
            'authors.*.name'            => 'nombre',
            'authors.*.surname'         => 'apellido',
            'authors.*.institution_id'  => 'institución',
            'authors.*.email'           => 'correo electrónico',
        ];
    }
}
