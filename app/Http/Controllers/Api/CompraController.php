<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\StorePagoCompraRequest;
use App\Models\Compra;
use App\Models\Usuario;
use App\Services\CompraService;
use Illuminate\Http\JsonResponse;

class CompraController extends Controller
{
    public function index(): JsonResponse
    {
        $compras = Compra::query()
            ->with(['proveedor', 'usuario', 'detalles.producto'])
            ->withSum('pagos as monto_pagado', 'monto')
            ->latest('fecha_compra')
            ->latest('id_compra')
            ->paginate(25);

        return response()->json([
            'status' => 'success',
            'data' => $compras,
        ]);
    }

    public function store(StoreCompraRequest $request, CompraService $compraService): JsonResponse
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 401);

        $compra = $compraService->registrarCompra($request->validated(), $usuario);

        return response()->json([
            'status' => 'success',
            'message' => 'Compra recibida; el inventario y los pagos fueron actualizados.',
            'data' => $compra,
        ], 201);
    }

    public function show(Compra $compra): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->cargarCompra($compra),
        ]);
    }

    public function storePayment(
        StorePagoCompraRequest $request,
        Compra $compra,
        CompraService $compraService
    ): JsonResponse {
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 401);

        $movimiento = $compraService->registrarPago(
            $compra,
            $request->validated('monto'),
            $request->validated('medio_pago'),
            $usuario
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Pago registrado en la caja de ventas.',
            'data' => [
                'pago' => $movimiento,
                'compra' => $this->cargarCompra($compra->refresh()),
            ],
        ], 201);
    }

    private function cargarCompra(Compra $compra): Compra
    {
        return $compra
            ->load(['proveedor', 'usuario', 'detalles.producto', 'pagos.usuario', 'pagos.caja'])
            ->loadSum('pagos as monto_pagado', 'monto');
    }
}
