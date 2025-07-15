<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleReviewRequest extends FormRequest
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
            'article_id'        => 'required|exists:articles,id',
            'editor_id'         => 'required|exists:users,id',
            'reviewers'         => 'nullable|array',
            'article_status_id' => 'required|exists:article_statuses,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'article_id'        => 'artículo',
            'editor_id'         => 'editor',
            'reviewers'         => 'revisores',
            'article_status_id' => 'estatus',
        ];
    }
}
