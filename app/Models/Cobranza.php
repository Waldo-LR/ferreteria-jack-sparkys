<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobranza extends Model
{
    use HasFactory;

    public const TIPO_ALQUILER = 'alquiler';

    public const TIPO_MORA = 'mora';

    public const TIPO_DANO = 'dano';

    protected $table = 'tcobranzas';

    protected $primaryKey = 'id_cobranza';

    protected $fillable = [
        'id_alquiler',
        'id_usuario',
        'tipo',
        'dias_atraso',
        'porcentaje_aplicado',
        'monto_mora',
        'monto_pagado',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'monto_mora' => 'decimal:2',
            'monto_pagado' => 'decimal:2',
            'porcentaje_aplicado' => 'decimal:2',
        ];
    }

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class, 'id_alquiler', 'id_alquiler');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function pagos()
    {
        return $this->hasMany(PagoCobranza::class, 'id_cobranza', 'id_cobranza');
    }
}
