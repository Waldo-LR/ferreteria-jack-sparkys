<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alquiler extends Model
{
    use HasFactory;

    protected $table = 'talquileres';

    protected $primaryKey = 'id_alquiler';

    protected $fillable = [
        'id_caja',
        'id_cliente',
        'id_producto',
        'id_usuario',
        'cantidad',
        'precio_dia',
        'dias_pactados',
        'fecha_entrega',
        'fecha_pactada_devolucion',
        'fecha_devolucion_real',
        'total_alquiler',
        'monto_pagado',
        'monto_garantia',
        'monto_garantia_aplicado',
        'monto_garantia_devuelto',
        'medio_pago',
        'medio_pago_garantia',
        'ubicacion_herramienta',
        'estado',
        'observaciones',
        'observaciones_devolucion',
        'id_usuario_devolucion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_entrega' => 'date',
            'fecha_pactada_devolucion' => 'date',
            'fecha_devolucion_real' => 'date',
            'precio_dia' => 'decimal:2',
            'total_alquiler' => 'decimal:2',
            'monto_pagado' => 'decimal:2',
            'monto_garantia' => 'decimal:2',
            'monto_garantia_aplicado' => 'decimal:2',
            'monto_garantia_devuelto' => 'decimal:2',
        ];
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja', 'id_caja');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function cobranzas(): HasMany
    {
        return $this->hasMany(Cobranza::class, 'id_alquiler', 'id_alquiler');
    }
}
