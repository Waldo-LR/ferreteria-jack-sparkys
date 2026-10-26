<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoCobranza extends Model
{
    use HasFactory;

    protected $table = 'tpagoscobranza';
    protected $primaryKey = 'id_pago';
    protected $fillable = ['id_cobranza', 'id_usuario', 'id_caja', 'monto', 'medio_pago', 'observaciones'];
    public function cobranza() { return $this->belongsTo(Cobranza::class, 'id_cobranza', 'id_cobranza'); }
    public function usuario() { return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario'); }
}
