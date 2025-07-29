<?php

namespace App\Http\Requests;

use App\Rules\ValidatedCall;
use App\Rules\ExactlyOneCorrespondingAuthor;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
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
            'title'                     => 'required|unique:articles|max:2555',
            'abstract'                  => 'required|max:10000',
            'key_works'                 => 'required|max:255',
            'knowledge_area_id'         => 'required|exists:knowledge_areas,id',
            'article_status_id'         => 'required|exists:article_statuses,id',
            'call_id'                   => [
                'required',
                'exists:calls,id',
                new ValidatedCall()
            ],
            'file'                      => 'required|mimes:pdf',

            'authors' => ['required', 'array', new ExactlyOneCorrespondingAuthor()],
            'authors.*.prefix'          => 'required|max:255',
            'authors.*.name'            => 'required|max:255',
            'authors.*.surname'         => 'required|max:255',
            'authors.*.institution_id'  => 'required|exists:institutions,id',
            'authors.*.email'           => 'required|email|max:255',
            'article_type_id' => 'required|exists:article_types,id',
            'authors.*.is_corresponding' => 'nullable|boolean',

        ];
    }
    public function attributes(): array
    {
        return [
            'title'                     => 'titulo',
            'abstract'                  => 'resumén',
            'key_works'                 => 'palabras clave',
            'knowledge_area_id'         => 'áreas prioritarias',
            'call_id'                   => 'convocatoria',
            'file'                      => 'archivo',
            'article_type_id' => 'tipo de artículo',

            'authors.*.is_corresponding' => 'autor correspondencia',
            'authors.*.prefix'          => 'prefijo',
            'authors.*.name'            => 'nombre',
            'authors.*.surname'         => 'apellido',
            'authors.*.institution_id'  => 'institución',
            'authors.*.email'           => 'correo electrónico',
        ];
    }
}
