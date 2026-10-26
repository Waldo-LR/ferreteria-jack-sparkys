<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'tcaja';
    protected $primaryKey = 'id_caja';
    protected $fillable = ['id_usuario', 'fecha_apertura', 'fecha_cierre', 'monto_inicial', 'total_ingresos', 'ingresos_efectivo', 'total_egresos', 'monto_final', 'tipo_cierre', 'estado', 'observaciones'];
    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario'); }
    public function gastos() { return $this->hasMany(GastoCaja::class, 'id_caja', 'id_caja'); }
}
