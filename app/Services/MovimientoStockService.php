<?php

namespace App\Services;

use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MovimientoStockService
{
    private const STOCK_MAXIMO = 4294967295;

    public function registrar(
        Producto $producto,
        Usuario $usuario,
        string $tipo,
        int $cantidad,
        string $motivo,
        ?string $referencia = null,
        bool $permitirProductoInactivo = false
    ): MovimientoStock {
        return DB::transaction(function () use ($producto, $usuario, $tipo, $cantidad, $motivo, $referencia, $permitirProductoInactivo): MovimientoStock {
            $productoBloqueado = Producto::query()
                ->lockForUpdate()
                ->findOrFail($producto->id_producto);
            abort_unless($productoBloqueado->activo || $permitirProductoInactivo, 404);

            if (! in_array($tipo, ['entrada', 'salida', 'ajuste'], true)) {
                throw ValidationException::withMessages([
                    'tipo' => 'El tipo de movimiento no es válido.',
                ]);
            }

            if ($cantidad < 0 || ($tipo !== 'ajuste' && $cantidad === 0) || $cantidad > self::STOCK_MAXIMO) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad indicada no es válida.',
                ]);
            }

            $stockAnterior = (int) $productoBloqueado->stock;
            $stockResultante = match ($tipo) {
                'entrada' => $stockAnterior + $cantidad,
                'salida' => $stockAnterior - $cantidad,
                'ajuste' => $cantidad,
            };

            if ($tipo === 'salida' && $cantidad > $stockAnterior) {
                throw ValidationException::withMessages([
                    'cantidad' => "Stock insuficiente. Disponible: {$stockAnterior} unidades.",
                ]);
            }

            if ($stockResultante > self::STOCK_MAXIMO) {
                throw ValidationException::withMessages([
                    'cantidad' => 'El stock resultante excede el máximo permitido.',
                ]);
            }

            if ($tipo === 'ajuste') {
                $cantidad = abs($stockResultante - $stockAnterior);

                if ($cantidad === 0) {
                    throw ValidationException::withMessages([
                        'cantidad' => 'El conteo ingresado coincide con el stock actual; no se registró ningún ajuste.',
                    ]);
                }

                $motivo = "Ajuste de {$stockAnterior} a {$stockResultante}: {$motivo}";
            }

            $productoBloqueado->update(['stock' => $stockResultante]);

            return MovimientoStock::create([
                'id_producto' => $productoBloqueado->id_producto,
                'id_usuario' => $usuario->id_usuario,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'stock_resultante' => $stockResultante,
                'motivo' => $motivo,
                'referencia' => $referencia,
            ]);
        }, 3);
    }
}
