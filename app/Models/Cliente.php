<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'tclientes';
    protected $primaryKey = 'id_cliente';
    protected $fillable = ['tipo_documento', 'dni_ruc', 'nombres', 'email', 'telefono', 'direccion', 'fecha_nacimiento', 'activo', 'fecha_baja'];
    public function ventas() { return $this->hasMany(Venta::class, 'id_cliente', 'id_cliente'); }
    public function alquileres() { return $this->hasMany(Alquiler::class, 'id_cliente', 'id_cliente'); }
}
