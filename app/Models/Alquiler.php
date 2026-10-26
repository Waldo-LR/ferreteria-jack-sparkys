<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alquiler extends Model
{
    use HasFactory;

    protected $table = 'talquileres';
    protected $primaryKey = 'id_alquiler';
    protected $fillable = ['id_caja', 'id_cliente', 'id_producto', 'id_usuario', 'cantidad', 'precio_dia', 'dias_pactados', 'fecha_entrega', 'fecha_pactada_devolucion', 'fecha_devolucion_real', 'total_alquiler', 'monto_pagado', 'medio_pago', 'ubicacion_herramienta', 'estado', 'observaciones', 'observaciones_devolucion', 'id_usuario_devolucion'];
    public function cliente() { return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente'); }
    public function producto() { return $this->belongsTo(Producto::class, 'id_producto', 'id_producto'); }
    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario'); }
}
