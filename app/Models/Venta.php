<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'tventas';
    protected $primaryKey = 'id_venta';
    protected $fillable = ['id_caja', 'id_cliente', 'id_usuario', 'tipo_comprobante', 'serie', 'numero', 'fecha_emision', 'medio_pago', 'valor_venta', 'igv', 'total', 'monto_pagado', 'vuelto', 'estado', 'estado_sunat', 'motivo_anulacion'];
    public function caja() { return $this->belongsTo(Caja::class, 'id_caja', 'id_caja'); }
    public function cliente() { return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente'); }
    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario'); }
    public function detalles() { return $this->hasMany(DetalleVenta::class, 'id_venta', 'id_venta'); }
}
