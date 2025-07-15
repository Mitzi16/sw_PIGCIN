<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCallRequest extends FormRequest
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
            'title'        => 'required|string|max:2555|unique:calls,title,' . $this->id,
            'description' => 'required|max:10000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'url' => 'nullable|url:http,https',
            'status' => 'required|boolean',
            'image_id' => 'required',
            'file_id' => 'required',
            'image' => 'nullable|extensions:jpeg,jpg,png|mimes:jpeg,jpg,png|max:10000',
            'file' => 'nullable|extensions:pdf|mimes:pdf|max:10000',
            'institution_id' => 'required|exists:institutions,id'
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'start_date' => 'fecha inicio',
            'end_date' => 'fecha fin',
            'url' => 'url',
            'image' => 'imagen',
            'file' => 'pdf',
            'institution_id' => 'institución'
        ];
    }
}
