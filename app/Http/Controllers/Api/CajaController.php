<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Caja;

class CajaController extends Controller
{
    public function index()
    {
        $data = Caja::all();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }

    public function show($id)
    {
        $item = Caja::find($id);
        if (! $item) {
            return response()->json(['status' => 'error', 'message' => 'Registro no encontrado.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $item], 200);
    }
}
