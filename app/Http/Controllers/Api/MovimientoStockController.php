<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\MovimientoStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MovimientoStockController extends Controller
{
    public function index(): JsonResponse
    {
        $data = MovimientoStock::query()
            ->with(['producto:id_producto,nombre,codigo', 'usuario:id_usuario,nombre'])
            ->latest('created_at')
            ->latest('id_movimiento')
            ->paginate(50);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $movimiento = MovimientoStock::query()
            ->with(['producto:id_producto,nombre,codigo', 'usuario:id_usuario,nombre'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $movimiento,
        ]);
    }

    public function storeForProduct(
        Request $request,
        Producto $producto,
        MovimientoStockService $movimientoStockService
    ): JsonResponse {
        $datos = $request->validate([
            'tipo' => ['required', Rule::in(['entrada', 'salida', 'ajuste'])],
            'cantidad' => [
                'required',
                'integer',
                Rule::when($request->input('tipo') === 'ajuste', ['min:0', 'max:4294967295'], ['min:1', 'max:4294967295']),
            ],
            'motivo' => ['required', 'string', 'max:150'],
            'referencia' => ['nullable', 'string', 'max:100'],
        ]);
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 401);

        $movimiento = $movimientoStockService->registrar(
            $producto,
            $usuario,
            $datos['tipo'],
            (int) $datos['cantidad'],
            $datos['motivo'],
            $datos['referencia'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Movimiento de inventario registrado correctamente.',
            'data' => $movimiento->load(['producto', 'usuario']),
        ], 201);
    }
}
