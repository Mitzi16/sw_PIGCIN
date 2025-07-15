<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleReviewRequest extends FormRequest
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
            'comment'           => 'required|max:2555',
            'reviewer_id'       => 'required|exists:users,id',
            'article_status_id' => 'required|exists:article_statuses,id',
            'article_id'        => 'required|exists:articles,id',
            'criteria'          => 'nullable|array',
        ];
    }

    public function attributes(): array
    {
        return [
            'comment'           => 'comentarios',
            'reviewer_id'       => 'revisor',
            'article_status_id' => 'resultado',
            'article_id'        => 'artículo',
            'criteria'          => 'criterios',
        ];
    }
}
