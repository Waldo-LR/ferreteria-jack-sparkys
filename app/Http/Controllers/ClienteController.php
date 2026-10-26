<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::where("activo", 1)->get();
        return view("clientes.index", compact("clientes"));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "tipo_documento" => "required|string",
            "dni_ruc" => "required|string|max:20",
            "nombres" => "required|string|max:150",
            "email" => "nullable|email",
            "telefono" => "nullable|string|max:20",
            "direccion" => "nullable|string|max:200",
        ]);
        $validated["activo"] = 1;
        Cliente::create($validated);
        return redirect()->route("clientes.index")->with("success", "Cliente registrado exitosamente.");
    }

    public function destroy($id)
    {
        Cliente::where("id_cliente", $id)->update(["activo" => 0]);
        return redirect()->route("clientes.index")->with("success", "Cliente removido.");
    }
}
