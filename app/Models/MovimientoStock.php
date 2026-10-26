<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoStock extends Model
{
    use HasFactory;

    protected $table = 'tmovimientosstock';
    protected $primaryKey = 'id_movimiento';
    protected $fillable = ['id_producto', 'id_usuario', 'tipo', 'cantidad', 'stock_resultante', 'motivo', 'referencia'];
    public function producto() { return $this->belongsTo(Producto::class, 'id_producto', 'id_producto'); }
    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario'); }
}
