<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MovimientoCaja;

class GastoCajaController extends Controller
{
    public function index()
    {
        $data = MovimientoCaja::all();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }

    public function show($id)
    {
        $item = MovimientoCaja::find($id);
        if (! $item) {
            return response()->json(['status' => 'error', 'message' => 'Registro no encontrado.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $item], 200);
    }
}
