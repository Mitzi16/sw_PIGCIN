<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidatePaymentVoucherRequest extends FormRequest
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
            'payment_voucher_status_id'     => 'required|exists:payment_voucher_statuses,id',
            'comments'                      => 'nullable|max:2555',
        ];
    }
    public function attributes(): array
    {
        return [
            'payment_voucher_status_id'     => 'estatus comprobante',
            'comments'                      => 'comentarios',
        ];
    }
}
