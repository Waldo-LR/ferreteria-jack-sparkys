<?php

namespace App\Http\Requests;

use App\Models\Caja;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePagoCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof Usuario && ($user->esAdmin() || $user->esAlmacenero());
    }

    public function rules(): array
    {
        return [
            'monto' => ['required', 'numeric', 'gt:0', 'max:9999999999.99', 'decimal:0,2'],
            'medio_pago' => ['required', Rule::in(Caja::MEDIOS_PAGO)],
        ];
    }
}
