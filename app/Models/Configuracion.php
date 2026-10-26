<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'tconfiguracion';
    protected $primaryKey = 'id_configuracion';
    protected $fillable = ['razon_social', 'ruc', 'direccion', 'telefono', 'email', 'slogan', 'igv_porcentaje', 'mora_porcentaje', 'dias_recuperacion', 'dias_alerta_vencimiento', 'cierre_automatico'];

}
