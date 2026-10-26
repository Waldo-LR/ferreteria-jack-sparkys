<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::where("activo", 1)->get();
        return view("categorias.index", compact("categorias"));
    }

    public function store(Request $request)
    {
        $request->validate(["nombre" => "required|string|max:100"]);
        Categoria::create(["nombre" => $request->nombre, "activo" => 1]);
        return redirect()->route("categorias.index")->with("success", "Categoría agregada con éxito.");
    }

    public function destroy($id)
    {
        Categoria::where("id_categoria", $id)->update(["activo" => 0]);
        return redirect()->route("categorias.index")->with("success", "Categoría eliminada.");
    }
}
