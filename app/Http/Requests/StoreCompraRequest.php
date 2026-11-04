<?php

namespace App\Http\Requests;

use App\Models\Caja;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof Usuario && ($user->esAdmin() || $user->esAlmacenero());
    }

    public function rules(): array
    {
        return [
            'id_proveedor' => [
                'required',
                'integer',
                Rule::exists('tproveedores', 'id_proveedor')->where('activo', true),
            ],
            'tipo_comprobante' => ['required', 'string', 'max:40'],
            'numero_comprobante' => ['required', 'string', 'max:50'],
            'fecha_compra' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'detalles' => ['required', 'array', 'min:1', 'max:100'],
            'detalles.*.id_producto' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('tproductos', 'id_producto')->where('activo', true),
            ],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'detalles.*.precio_unitario' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999.99',
                'decimal:0,2',
            ],
            'monto_pagado' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'medio_pago' => [
                'nullable',
                Rule::requiredIf(fn (): bool => (float) $this->input('monto_pagado', 0) > 0),
                Rule::in(Caja::MEDIOS_PAGO),
            ],
        ];
    }
}
