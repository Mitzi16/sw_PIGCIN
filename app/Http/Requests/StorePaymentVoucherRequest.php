<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Esta función es perfecta y define la lógica principal.
        $requiresManualBilling = function () {
            return $this->input('requires_invoice') === 'yes' && !$this->hasFile('constancia');
        };

        return [
            'article_id'                => 'required|exists:articles,id',
            'reference'                 => 'required|string|max:255',
            'amount'                    => 'required|numeric|gt:0|lte:100000',
            'file'                      => 'required|mimes:jpeg,png,jpg,pdf|max:10000',
            'payment_voucher_status_id' => 'nullable|exists:payment_voucher_statuses,id',
            'user_id'                   => 'nullable|exists:users,id',

            // Facturación
            'requires_invoice'          => 'nullable|string|in:yes,no',
            
        
            'constancia'                => 'nullable|mimes:pdf|max:10000',

            
            'rfc'                       => ['nullable', 'string', 'max:255', Rule::requiredIf($requiresManualBilling)],
            'direccion_fiscal'          => ['nullable', 'string', 'max:500', Rule::requiredIf($requiresManualBilling)],
            'regimen_fiscal'            => ['nullable', 'string', 'max:255', Rule::requiredIf($requiresManualBilling)],
            'uso_cfdi'                  => ['nullable', 'string', 'max:255', Rule::requiredIf($requiresManualBilling)],
        ];
    }

    public function attributes(): array
    {
        return [
            'article_id'       => 'artículo',
            'reference'        => 'referencia',
            'amount'           => 'monto',
            'payment_voucher_status_id' => 'estatus del comprobante',
            'user_id'          => 'usuario',
            'file'             => 'archivo',
            'requires_invoice' => '¿requiere factura?',
            'constancia'       => 'constancia de situación fiscal',
            'rfc'              => 'RFC',
            'direccion_fiscal' => 'dirección fiscal',
            'regimen_fiscal'   => 'régimen fiscal',
            'uso_cfdi'         => 'uso de CFDI',
        ];
    }
}