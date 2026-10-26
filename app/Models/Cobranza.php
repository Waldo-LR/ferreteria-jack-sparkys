<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobranza extends Model
{
    use HasFactory;

    protected $table = 'tcobranzas';
    protected $primaryKey = 'id_cobranza';
    protected $fillable = ['id_alquiler', 'id_usuario', 'dias_atraso', 'monto_mora', 'monto_pagado', 'estado', 'observaciones'];
    public function alquiler() { return $this->belongsTo(Alquiler::class, 'id_alquiler', 'id_alquiler'); }
    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario'); }
    public function pagos() { return $this->hasMany(PagoCobranza::class, 'id_cobranza', 'id_cobranza'); }
}
