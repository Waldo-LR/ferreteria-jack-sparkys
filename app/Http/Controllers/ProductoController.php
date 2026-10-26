<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with(["categoria", "marca"])->where("activo", 1)->orderBy("nombre")->get();
        $categorias = Categoria::where("activo", 1)->get();
        $marcas = Marca::where("activo", 1)->get();
        return view("productos.index", compact("productos", "categorias", "marcas"));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "codigo" => "required|string|max:50",
            "id_categoria" => "required|integer",
            "id_marca" => "required|integer",
            "nombre" => "required|string|max:150",
            "descripcion" => "nullable|string",
            "stock" => "required|integer|min:0",
            "stock_minimo" => "required|integer|min:0",
            "precio_compra" => "required|numeric|min:0",
            "precio_venta" => "required|numeric|min:0",
            "precio_venta_mayor" => "nullable|numeric|min:0",
            "cantidad_mayorista" => "nullable|integer|min:0",
            "es_alquilable" => "nullable|boolean",
            "precio_alquiler_dia" => "nullable|numeric|min:0",
        ]);

        $validated["activo"] = 1;
        $validated["es_alquilable"] = $request->has("es_alquilable") ? 1 : 0;
        Producto::create($validated);

        return redirect()->route("productos.index")->with("success", "Producto registrado correctamente en el inventario.");
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update(["activo" => 0]);
        return redirect()->route("productos.index")->with("success", "Producto dado de baja correctamente.");
    }
}
