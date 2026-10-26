<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'tnotificaciones';
    protected $primaryKey = 'id_notificacion';
    protected $fillable = ['id_producto', 'id_cliente', 'id_usuario', 'tipo', 'atendida'];
    public function producto() { return $this->belongsTo(Producto::class, 'id_producto', 'id_producto'); }
    public function cliente() { return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente'); }
}
