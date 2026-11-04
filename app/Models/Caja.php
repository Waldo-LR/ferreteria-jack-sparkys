<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    public const TIPO_VENTAS = 'ventas';

    public const TIPO_ALQUILERES = 'alquileres';

    public const MEDIOS_PAGO = ['efectivo', 'yape', 'deposito'];

    protected $table = 'tcaja';

    protected $primaryKey = 'id_caja';

    protected $fillable = [
        'id_usuario',
        'id_usuario_cierre',
        'tipo_caja',
        'tipo_caja_abierta',
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'total_ingresos',
        'ingresos_efectivo',
        'total_egresos',
        'egresos_efectivo',
        'monto_final',
        'monto_contado',
        'diferencia_cierre',
        'tipo_cierre',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'datetime',
            'fecha_cierre' => 'datetime',
            'monto_inicial' => 'decimal:2',
            'total_ingresos' => 'decimal:2',
            'ingresos_efectivo' => 'decimal:2',
            'total_egresos' => 'decimal:2',
            'egresos_efectivo' => 'decimal:2',
            'monto_final' => 'decimal:2',
            'monto_contado' => 'decimal:2',
            'diferencia_cierre' => 'decimal:2',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function usuarioCierre()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_cierre', 'id_usuario');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'id_caja', 'id_caja');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_caja', 'id_caja');
    }

    public function alquileres()
    {
        return $this->hasMany(Alquiler::class, 'id_caja', 'id_caja');
    }

    public function pagosCobranza()
    {
        return $this->hasMany(PagoCobranza::class, 'id_caja', 'id_caja');
    }
}
