<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'tcompras';
    protected $primaryKey = 'id_compra';
    protected $fillable = ['id_proveedor', 'id_usuario', 'tipo_comprobante', 'numero_comprobante', 'fecha_compra', 'total', 'estado', 'observaciones'];
    public function proveedor() { return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor'); }
    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario'); }
    public function detalles() { return $this->hasMany(DetalleCompra::class, 'id_compra', 'id_compra'); }
}
