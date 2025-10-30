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
       return [
            'reference' => 'required|string|max:255',
            'amount'    => 'required|numeric|gt:0|lte:100000',

            // Se agrega 'file' como 'nullable'.
            // 'nullable' significa que si no se envía un archivo nuevo,
            // la validación pasa. Si SÍ se envía, debe ser pdf/max:10MB.
            'file'      => 'nullable|mimes:pdf|max:10000',

            // --- CAMPOS ELIMINADOS DE LA VALIDACIÓN ---
            // 'editable_file'
            // 'references'
            // Todos los campos de facturación (requires_invoice, rfc, etc.)
        ];
    }
    
    public function attributes(): array
    {
       return [
            'reference' => 'referencia',
            'amount'    => 'monto',
            'file'      => 'archivo', // <-- El comprobante
        ];
    }
}