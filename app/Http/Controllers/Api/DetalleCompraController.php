<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleCompra;
use Illuminate\Http\JsonResponse;

class DetalleCompraController extends Controller
{
    public function index(): JsonResponse
    {
        $data = DetalleCompra::query()
            ->with([
                'compra:id_compra,numero_comprobante,fecha_compra,total',
                'producto:id_producto,codigo,nombre',
            ])
            ->latest('id_detalle_compra')
            ->paginate(50);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $detalle = DetalleCompra::query()
            ->with(['compra', 'producto'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $detalle,
        ]);
    }
}
