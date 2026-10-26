<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tusuarios';
    protected $primaryKey = 'id_usuario';
    protected $fillable = [
        'id_rol', 'nombre', 'usuario', 'email', 'password',
        'sexo', 'telefono', 'direccion', 'sobre_mi', 'foto',
        'activo', 'fecha_baja', 'ultimo_login'
    ];
    protected $hidden = ['password', 'remember_token'];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function esAdmin()
    {
        return $this->id_rol == 1;
    }

    public function esVendedor()
    {
        return $this->id_rol == 2;
    }

    public function esAlmacenero()
    {
        return $this->id_rol == 3;
    }

    public function esCobrador()
    {
        return $this->id_rol == 4;
    }

    public function esCajero()
    {
        return $this->id_rol == 5;
    }
}

