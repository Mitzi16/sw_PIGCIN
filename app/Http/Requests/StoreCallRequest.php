<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallRequest extends FormRequest
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
            'title'             => 'required|string|unique:calls,title|max:2555',
            'description'       => 'required|max:10000',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after:start_date',
            'status'            => 'required|boolean',
            'url'               => 'nullable|url:http,https',
            'image'             => 'required|extensions:jpeg,jpg,png|mimes:jpeg,jpg,png|max:10000',
            'file'              => 'required|extensions:pdf|mimes:pdf|max:10000',
            'institution_id'    => 'required|exists:institutions,id'
        ];
    }

    public function attributes(): array
    {
        return [
            'title'             => 'título',
            'description'       => 'descripción',
            'start_date'        => 'fecha inicio',
            'end_date'          => 'fecha fin',
            'url'               => 'url',
            'image'             => 'imagen',
            'file'              => 'pdf',
            'institution_id'    => 'institución'
        ];
    }
}
