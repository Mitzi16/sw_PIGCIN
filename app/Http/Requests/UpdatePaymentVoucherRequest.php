<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtenemos el registro actual que se está actualizando
        $paymentVoucher = $this->route('payment_voucher');

        // LA FORMA CORRECTA DE REVISAR, USANDO TU RELACIÓN:
        // Si la relación ->constancia devuelve un modelo, significa que ya existe un archivo.
        $hasExistingConstancia = !empty($paymentVoucher->constancia);

        $requiresManualBilling = function () use ($hasExistingConstancia) {
            return $this->input('requires_invoice') === 'yes'   // Quiere factura
                && !$this->hasFile('constancia')                // Y no sube un archivo nuevo
                && !$hasExistingConstancia;                     // Y no tiene uno guardado previamente
        };

        return [
            // ... (otras reglas se quedan igual) ...
            'reference'                 => 'required|string|max:255',
            'amount'                    => 'required|numeric|gt:0|lte:100000',
            
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