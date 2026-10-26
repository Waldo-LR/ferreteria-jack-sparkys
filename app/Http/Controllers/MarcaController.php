<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::where("activo", 1)->get();
        return view("marcas.index", compact("marcas"));
    }

    public function store(Request $request)
    {
        $request->validate(["nombre" => "required|string|max:100"]);
        Marca::create(["nombre" => $request->nombre, "activo" => 1]);
        return redirect()->route("marcas.index")->with("success", "Marca agregada con éxito.");
    }

    public function destroy($id)
    {
        Marca::where("id_marca", $id)->update(["activo" => 0]);
        return redirect()->route("marcas.index")->with("success", "Marca eliminada.");
    }
}
