<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index()
    {
        $data = Notificacion::all();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $item = Notificacion::create($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Registro creado correctamente.',
            'data' => $item
        ], 201);
    }

    public function show($id)
    {
        $item = Notificacion::find($id);
        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Registro no encontrado.'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $item], 200);
    }

    public function update(Request $request, $id)
    {
        $item = Notificacion::find($id);
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
        $item = Notificacion::find($id);
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
