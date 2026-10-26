<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'tauditoria';
    protected $primaryKey = 'id_auditoria';
    protected $fillable = ['id_usuario', 'accion', 'tabla_afectada', 'id_registro', 'detalle', 'ip'];
    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario'); }
}
