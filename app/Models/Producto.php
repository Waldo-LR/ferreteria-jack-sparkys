<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'tproductos';
    protected $primaryKey = 'id_producto';
    protected $fillable = ['codigo', 'id_categoria', 'id_marca', 'nombre', 'descripcion', 'stock', 'stock_minimo', 'precio_compra', 'precio_venta', 'precio_venta_mayor', 'cantidad_mayorista', 'es_alquilable', 'precio_alquiler_dia', 'fecha_vencimiento', 'imagen', 'activo', 'fecha_baja'];
    public function categoria() { return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria'); }
    public function marca() { return $this->belongsTo(Marca::class, 'id_marca', 'id_marca'); }
}
