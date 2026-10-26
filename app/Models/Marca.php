<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;

    protected $table = 'tmarcas';
    protected $primaryKey = 'id_marca';
    protected $fillable = ['nombre', 'activo', 'fecha_baja'];
    public function productos() { return $this->hasMany(Producto::class, 'id_marca', 'id_marca'); }
}
