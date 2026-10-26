<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoCaja extends Model
{
    use HasFactory;

    protected $table = 'tgastoscaja';
    protected $primaryKey = 'id_gasto';
    protected $fillable = ['id_caja', 'id_usuario', 'categoria', 'monto', 'detalle'];
    public function caja() { return $this->belongsTo(Caja::class, 'id_caja', 'id_caja'); }
    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario'); }
}
