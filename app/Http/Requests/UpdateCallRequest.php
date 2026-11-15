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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|boolean',
            'url' => 'nullable|url',
            'file' => 'nullable|mimes:pdf|max:10240', // Máximo 10MB
            'image' => 'nullable|image|max:5120', // Máximo 5MB (jpeg, png, jpg, gif, svg)
            'image_id' => 'nullable|exists:files,id',
            'file_id' => 'nullable|exists:files,id',
            'institution_id' => 'required|exists:institutions,id',

            // REGLAS NUEVAS PARA EL CALENDARIO
            'publication_start' => 'nullable|date',
            'publication_end' => 'nullable|date|after_or_equal:publication_start',

            'registration_start' => 'nullable|date',
            'registration_end' => 'nullable|date|after_or_equal:registration_start',

            'application_start' => 'nullable|date',
            'application_end' => 'nullable|date|after_or_equal:application_start',

            'evaluation_start' => 'nullable|date',
            'evaluation_end' => 'nullable|date|after_or_equal:evaluation_start',

            'results_start' => 'nullable|date',
            'results_end' => 'nullable|date|after_or_equal:results_start',
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
