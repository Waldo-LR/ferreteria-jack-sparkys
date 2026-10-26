<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    use HasFactory;

    protected $table = 'tseries';
    protected $primaryKey = 'id_serie';
    protected $fillable = ['tipo_comprobante', 'serie', 'ultimo_numero', 'activo'];

}
