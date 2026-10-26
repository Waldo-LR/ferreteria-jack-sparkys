<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'troles';
    protected $primaryKey = 'id_rol';
    protected $fillable = ['nombre', 'descripcion', 'activo'];
    public function usuarios() { return $this->hasMany(Usuario::class, 'id_rol', 'id_rol'); }
}
