<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_compra
 */
class Compra extends Model
{
    use HasFactory;

    protected $table = 'tcompras';

    protected $primaryKey = 'id_compra';

    protected $fillable = ['id_proveedor', 'id_usuario', 'tipo_comprobante', 'numero_comprobante', 'fecha_compra', 'total', 'estado', 'observaciones'];

    protected $appends = ['monto_pagado', 'saldo_pendiente', 'estado_pago'];

    protected function casts(): array
    {
        return [
            'fecha_compra' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra', 'id_compra');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'id_compra', 'id_compra')
            ->where('categoria', 'Pago a proveedor');
    }

    public function getMontoPagadoAttribute(): float
    {
        if (array_key_exists('monto_pagado', $this->attributes)) {
            return (float) $this->attributes['monto_pagado'];
        }

        if ($this->relationLoaded('pagos')) {
            return (float) $this->pagos->sum('monto');
        }

        return (float) $this->pagos()->sum('monto');
    }

    public function getSaldoPendienteAttribute(): float
    {
        if ($this->estado !== 'recibida') {
            return 0.0;
        }

        return round(max((float) $this->total - $this->monto_pagado, 0), 2);
    }

    public function getEstadoPagoAttribute(): string
    {
        if ($this->estado !== 'recibida') {
            return 'sin_conciliar';
        }

        if ($this->saldo_pendiente <= 0) {
            return 'pagada';
        }

        return $this->monto_pagado > 0 ? 'parcial' : 'pendiente';
    }
}
