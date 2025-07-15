<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentVoucherRequest extends FormRequest
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
            'article_id'                    => 'required|exists:articles,id',
            'reference'                     => 'required|string|max:255',
            'amount'                        => 'required|numeric|gt:' . 0 . '|lte:' . 100000,
            'payment_voucher_status_id'     => 'nullable|exists:payment_voucher_statuses,id',
            'user_id'                       => 'nullable|exists:users,id',
            'file'                          => 'required|mimes:jpeg,png,jpg,pdf|max:10000',
        ];
    }
    public function attributes(): array
    {
        return [
            'article_id'                    => 'articulo',
            'reference'                     => 'referencia',
            'amount'                        => 'monto',
            'payment_voucher_status_id'     => 'comprobante estatus',
            'user_id'                       => 'usuario',
            'file'                          => 'archivo',
        ];
    }
}
