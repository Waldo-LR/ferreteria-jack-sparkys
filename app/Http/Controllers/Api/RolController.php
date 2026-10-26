<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        $data = Rol::all();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $item = Rol::create($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Registro creado correctamente.',
            'data' => $item
        ], 201);
    }

    public function show($id)
    {
        $item = Rol::find($id);
        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Registro no encontrado.'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $item], 200);
    }

    public function update(Request $request, $id)
    {
        $item = Rol::find($id);
        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Registro no encontrado.'], 404);
        }
        $item->update($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Registro actualizado correctamente.',
            'data' => $item
        ], 200);
    }

    public function destroy($id)
    {
        $item = Rol::find($id);
        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Registro no encontrado.'], 404);
        }
        $item->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Registro eliminado correctamente.'
        ], 200);
    }
}
