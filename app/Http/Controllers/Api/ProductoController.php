<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\MovimientoStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

class ProductoController extends Controller
{
    public function index(): JsonResponse
    {
        $data = Producto::query()
            ->with(['categoria', 'marca'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->paginate(50);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function store(Request $request, MovimientoStockService $movimientoStockService): JsonResponse
    {
        $datos = $request->validate($this->rules());
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 401);
        $stockInicial = (int) $datos['stock'];
        unset($datos['stock']);

        $datos['precio_venta_mayor'] ??= $datos['precio_venta'];
        $datos['cantidad_mayorista'] ??= 2;
        $datos['es_alquilable'] = (bool) ($datos['es_alquilable'] ?? false);
        $datos['precio_alquiler_dia'] ??= 0;

        $item = DB::transaction(function () use ($datos, $stockInicial, $usuario, $movimientoStockService): Producto {
            $producto = Producto::create([
                ...$datos,
                'stock' => 0,
                'activo' => true,
            ]);

            if ($stockInicial > 0) {
                $movimientoStockService->registrar(
                    $producto,
                    $usuario,
                    'entrada',
                    $stockInicial,
                    'Stock inicial por creación de producto mediante API'
                );
            }

            return $producto->refresh()->load(['categoria', 'marca']);
        }, 3);

        return response()->json([
            'status' => 'success',
            'message' => 'Registro creado correctamente.',
            'data' => $item,
        ], 201);
    }

    public function show(Producto $producto): JsonResponse
    {
        abort_unless($producto->activo, 404);

        return response()->json([
            'status' => 'success',
            'data' => $producto->load(['categoria', 'marca']),
        ]);
    }

    public function update(Request $request, Producto $producto): JsonResponse
    {
        abort_unless($producto->activo, 404);
        $producto->update($request->validate($this->rules($producto, true)));

        return response()->json([
            'status' => 'success',
            'message' => 'Registro actualizado correctamente.',
            'data' => $producto->refresh()->load(['categoria', 'marca']),
        ]);
    }

    public function destroy(Producto $producto): JsonResponse
    {
        return DB::transaction(function () use ($producto): JsonResponse {
            $productoBloqueado = Producto::query()
                ->lockForUpdate()
                ->findOrFail($producto->id_producto);

            if ($productoBloqueado->stock > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede dar de baja un producto con existencias.',
                ], 422);
            }

            $productoBloqueado->update(['activo' => false, 'fecha_baja' => now()]);

            return response()->json([
                'status' => 'success',
                'message' => 'Producto dado de baja correctamente.',
            ]);
        }, 3);
    }

    /**
     * @return array<string, array<int, string|Exists|Unique>>
     */
    private function rules(?Producto $producto = null, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'codigo' => [
                $required,
                'string',
                'max:50',
                Rule::unique('tproductos', 'codigo')->ignore($producto?->id_producto, 'id_producto'),
            ],
            'id_categoria' => [
                $required,
                'integer',
                Rule::exists('tcategorias', 'id_categoria')->where('activo', true),
            ],
            'id_marca' => [
                $required,
                'integer',
                Rule::exists('tmarcas', 'id_marca')->where('activo', true),
            ],
            'nombre' => [$required, 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'stock' => $partial
                ? ['prohibited']
                : ['required', 'integer', 'min:0', 'max:4294967295'],
            'stock_minimo' => [$required, 'integer', 'min:0', 'max:4294967295'],
            'precio_compra' => $partial
                ? ['prohibited']
                : [$required, 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'precio_venta' => [$required, 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'precio_venta_mayor' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'cantidad_mayorista' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            'es_alquilable' => ['sometimes', 'boolean'],
            'precio_alquiler_dia' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ];
    }
}
