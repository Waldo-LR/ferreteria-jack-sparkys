<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoCaja extends Model
{
    use HasFactory;

    protected $table = 'tgastoscaja';

    protected $primaryKey = 'id_gasto';

    protected $fillable = [
        'id_caja',
        'id_usuario',
        'id_compra',
        'id_alquiler',
        'tipo_movimiento',
        'categoria',
        'monto',
        'medio_pago',
        'detalle',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
        ];
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'id_caja', 'id_caja');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function alquiler(): BelongsTo
    {
        return $this->belongsTo(Alquiler::class, 'id_alquiler', 'id_alquiler');
    }
}
