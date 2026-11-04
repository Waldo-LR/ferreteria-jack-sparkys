<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Caja;
use App\Models\Compra;
use App\Models\Configuracion;
use App\Models\MovimientoCaja;
use App\Models\MovimientoStock;
use App\Models\PagoCobranza;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReportesController extends Controller
{
    private const REPORTES = [
        'ventas' => 'Ventas',
        'alquileres' => 'Alquileres',
        'cobranzas' => 'Cobranzas',
        'caja' => 'Movimientos de Caja',
        'inventario' => 'Inventario',
        'compras' => 'Compras y Proveedores',
        'movimientos_inventario' => 'Movimientos de Inventario',
    ];

    public function index(Request $request): View
    {
        $this->authorizeAccess();

        $filters = $this->validatedFilters($request);
        $report = $this->buildReport($filters);

        return view('reportes.index', [
            'report' => $report,
            'filters' => $filters,
            'reportTypes' => self::REPORTES,
            'generatedAt' => now(),
        ]);
    }

    public function pdf(Request $request): Response
    {
        $this->authorizeAccess();

        $filters = $this->validatedFilters($request);
        $report = $this->buildReport($filters, true);
        $generatedAt = now();
        $configuracion = Configuracion::query()->first();
        $filename = 'reporte-'.$filters['tipo'].'-'.$filters['fecha_desde'].'-a-'.$filters['fecha_hasta'].'.pdf';

        return Pdf::loadView('reportes.pdf', [
            'report' => $report,
            'filters' => $filters,
            'generatedAt' => $generatedAt,
            'configuracion' => $configuracion,
        ])->setPaper('a4', 'landscape')->download($filename);
    }

    /**
     * @return array{tipo: string, fecha_desde: string, fecha_hasta: string}
     */
    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'tipo' => ['nullable', Rule::in(array_keys(self::REPORTES))],
            'fecha_desde' => ['nullable', 'date_format:Y-m-d', 'required_with:fecha_hasta'],
            'fecha_hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_desde', 'required_with:fecha_desde'],
        ]);

        $desde = $validated['fecha_desde'] ?? now()->startOfMonth()->toDateString();
        $hasta = $validated['fecha_hasta'] ?? now()->toDateString();

        if (Carbon::parse($desde)->diffInDays(Carbon::parse($hasta)) > 365) {
            throw ValidationException::withMessages([
                'fecha_desde' => 'El periodo máximo de consulta es de 366 días.',
            ]);
        }

        return [
            'tipo' => $validated['tipo'] ?? 'ventas',
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
        ];
    }

    /**
     * @param  array{tipo: string, fecha_desde: string, fecha_hasta: string}  $filters
     * @return array{titulo: string, columnas: array<int, string>, filas: iterable, resumen: array<int, array{etiqueta: string, valor: string}>, nota: string}
     */
    private function buildReport(array $filters, bool $forPdf = false): array
    {
        [$query, $columns, $summary, $note] = match ($filters['tipo']) {
            'ventas' => $this->salesReport($filters),
            'alquileres' => $this->rentalsReport($filters),
            'cobranzas' => $this->collectionsReport($filters),
            'caja' => $this->cashReport($filters),
            'inventario' => $this->inventoryReport(),
            'compras' => $this->purchasesReport($filters),
            'movimientos_inventario' => $this->stockMovementsReport($filters),
        };

        $totalRows = (clone $query)->count();

        if ($forPdf && $totalRows > 2500) {
            throw ValidationException::withMessages([
                'tipo' => 'El reporte supera los 2,500 registros. Reduce el periodo y vuelve a exportar.',
            ]);
        }

        $rows = $forPdf
            ? $query->get()->map(fn ($record): array => $this->formatRow($filters['tipo'], $record))
            : $query->paginate(25)->withQueryString()->through(
                fn ($record): array => $this->formatRow($filters['tipo'], $record)
            );

        return [
            'titulo' => 'Reporte de '.self::REPORTES[$filters['tipo']],
            'columnas' => $columns,
            'filas' => $rows,
            'resumen' => $summary,
            'nota' => $note,
        ];
    }

    /**
     * @param  array{tipo: string, fecha_desde: string, fecha_hasta: string}  $filters
     * @return array{Builder, array<int, string>, array<int, array{etiqueta: string, valor: string}>, string}
     */
    private function salesReport(array $filters): array
    {
        $query = Venta::query()
            ->with(['cliente:id_cliente,nombres', 'usuario:id_usuario,nombre'])
            ->whereBetween('fecha_emision', [$filters['fecha_desde'], $filters['fecha_hasta']])
            ->latest('fecha_emision')
            ->latest('id_venta');

        $summary = [
            $this->summaryItem('Comprobantes', number_format((clone $query)->count())),
            $this->summaryItem('Total vendido', $this->money((clone $query)->where('estado', 'emitida')->sum('total'))),
            $this->summaryItem('Anuladas', number_format((clone $query)->where('estado', 'anulada')->count())),
        ];

        return [
            $query,
            ['Comprobante', 'Cliente', 'Vendedor', 'Fecha', 'Pago', 'Total (S/)', 'Estado'],
            $summary,
            'El total vendido considera únicamente comprobantes emitidos; los anulados se muestran para consulta.',
        ];
    }

    /**
     * @param  array{tipo: string, fecha_desde: string, fecha_hasta: string}  $filters
     * @return array{Builder, array<int, string>, array<int, array{etiqueta: string, valor: string}>, string}
     */
    private function rentalsReport(array $filters): array
    {
        $query = Alquiler::query()
            ->with([
                'cliente:id_cliente,nombres',
                'producto:id_producto,nombre',
                'usuario:id_usuario,nombre',
            ])
            ->whereBetween('fecha_entrega', [$filters['fecha_desde'], $filters['fecha_hasta']])
            ->latest('fecha_entrega')
            ->latest('id_alquiler');

        return [
            $query,
            ['N° Alquiler', 'Equipo', 'Cliente', 'Responsable', 'Entrega', 'Pago', 'Total (S/)', 'Estado'],
            [
                $this->summaryItem('Alquileres', number_format((clone $query)->count())),
                $this->summaryItem('Importe registrado', $this->money((clone $query)->sum('total_alquiler'))),
                $this->summaryItem('Vigentes', number_format((clone $query)->where('estado', 'vigente')->count())),
            ],
            'Importes calculados sobre los contratos de alquiler registrados en el periodo.',
        ];
    }

    /**
     * @param  array{tipo: string, fecha_desde: string, fecha_hasta: string}  $filters
     * @return array{Builder, array<int, string>, array<int, array{etiqueta: string, valor: string}>, string}
     */
    private function collectionsReport(array $filters): array
    {
        $query = PagoCobranza::query()
            ->with([
                'usuario:id_usuario,nombre',
                'cobranza.alquiler.cliente',
            ])
            ->whereBetween('created_at', [
                Carbon::parse($filters['fecha_desde'])->startOfDay(),
                Carbon::parse($filters['fecha_hasta'])->endOfDay(),
            ])
            ->latest('created_at')
            ->latest('id_pago');

        return [
            $query,
            ['N° Pago', 'Cliente', 'N° Alquiler', 'Cobrador', 'Fecha', 'Pago', 'Monto (S/)', 'Observación'],
            [
                $this->summaryItem('Pagos registrados', number_format((clone $query)->count())),
                $this->summaryItem('Total recaudado', $this->money((clone $query)->sum('monto'))),
            ],
            'Incluye todos los abonos registrados durante el periodo para saldos de alquiler, moras y daños.',
        ];
    }

    /**
     * @param  array{tipo: string, fecha_desde: string, fecha_hasta: string}  $filters
     * @return array{Builder, array<int, string>, array<int, array{etiqueta: string, valor: string}>, string}
     */
    private function cashReport(array $filters): array
    {
        $query = MovimientoCaja::query()
            ->with(['caja:id_caja,tipo_caja', 'usuario:id_usuario,nombre'])
            ->whereBetween('created_at', [
                Carbon::parse($filters['fecha_desde'])->startOfDay(),
                Carbon::parse($filters['fecha_hasta'])->endOfDay(),
            ])
            ->latest('created_at')
            ->latest('id_gasto');

        return [
            $query,
            ['Tipo', 'Caja', 'Categoría', 'Detalle', 'Fecha', 'Medio', 'Responsable', 'Monto (S/)'],
            [
                $this->summaryItem('Movimientos', number_format((clone $query)->count())),
                $this->summaryItem('Ingresos', $this->money((clone $query)->where('tipo_movimiento', 'ingreso')->sum('monto'))),
                $this->summaryItem('Egresos', $this->money((clone $query)->where('tipo_movimiento', 'egreso')->sum('monto'))),
            ],
            'Incluye los ingresos y egresos de Control de Caja, como pagos a proveedores, garantías y cobranzas.',
        ];
    }

    /**
     * @return array{Builder, array<int, string>, array<int, array{etiqueta: string, valor: string}>, string}
     */
    private function inventoryReport(): array
    {
        $query = Producto::query()
            ->with([
                'categoria:id_categoria,nombre',
                'marca:id_marca,nombre',
            ])
            ->where('activo', true)
            ->orderBy('nombre')
            ->orderBy('id_producto');

        return [
            $query,
            ['Código', 'Producto', 'Categoría', 'Marca', 'Stock', 'Mínimo', 'Costo unit. (S/)', 'Valor stock (S/)'],
            [
                $this->summaryItem('Productos activos', number_format((clone $query)->count())),
                $this->summaryItem('Por reponer', number_format((clone $query)->whereColumn('stock', '<=', 'stock_minimo')->count())),
                $this->summaryItem(
                    'Valor al costo',
                    $this->money(
                        Producto::query()
                            ->where('activo', true)
                            ->selectRaw('COALESCE(SUM(stock * precio_compra), 0) as total')
                            ->value('total')
                    )
                ),
            ],
            'Inventario actual valorizado al costo de compra; no depende del periodo seleccionado.',
        ];
    }

    /**
     * @param  array{tipo: string, fecha_desde: string, fecha_hasta: string}  $filters
     * @return array{Builder, array<int, string>, array<int, array{etiqueta: string, valor: string}>, string}
     */
    private function purchasesReport(array $filters): array
    {
        $query = Compra::query()
            ->with([
                'proveedor:id_proveedor,razon_social,ruc',
                'usuario:id_usuario,nombre',
            ])
            ->withSum('pagos', 'monto')
            ->whereBetween('fecha_compra', [$filters['fecha_desde'], $filters['fecha_hasta']])
            ->latest('fecha_compra')
            ->latest('id_compra');

        $comprasRecibidas = Compra::query()
            ->where('estado', 'recibida')
            ->whereBetween('fecha_compra', [$filters['fecha_desde'], $filters['fecha_hasta']]);
        $totalRecibido = (float) (clone $comprasRecibidas)->sum('total');
        $totalPagado = (float) MovimientoCaja::query()
            ->where('categoria', 'Pago a proveedor')
            ->whereIn('id_compra', (clone $comprasRecibidas)->select('id_compra'))
            ->sum('monto');

        return [
            $query,
            ['N° Compra', 'Proveedor', 'RUC', 'Responsable', 'Fecha', 'Comprobante', 'Total (S/)', 'Pagado (S/)', 'Saldo (S/)', 'Estado'],
            [
                $this->summaryItem('Compras', number_format((clone $query)->count())),
                $this->summaryItem('Importe recibido', $this->money($totalRecibido)),
                $this->summaryItem('Pagado a proveedores', $this->money($totalPagado)),
                $this->summaryItem('Saldo pendiente', $this->money(max(round($totalRecibido - $totalPagado, 2), 0))),
            ],
            'Las compras se agrupan por fecha de compra; el pagado incluye todos los abonos registrados para esas compras.',
        ];
    }

    /**
     * @param  array{tipo: string, fecha_desde: string, fecha_hasta: string}  $filters
     * @return array{Builder, array<int, string>, array<int, array{etiqueta: string, valor: string}>, string}
     */
    private function stockMovementsReport(array $filters): array
    {
        $query = MovimientoStock::query()
            ->with([
                'producto:id_producto,codigo,nombre',
                'usuario:id_usuario,nombre',
            ])
            ->whereBetween('created_at', [
                Carbon::parse($filters['fecha_desde'])->startOfDay(),
                Carbon::parse($filters['fecha_hasta'])->endOfDay(),
            ])
            ->latest('created_at')
            ->latest('id_movimiento');

        return [
            $query,
            ['Fecha', 'Producto', 'Tipo', 'Cantidad', 'Stock resultante', 'Motivo', 'Referencia', 'Responsable'],
            [
                $this->summaryItem('Movimientos', number_format((clone $query)->count())),
                $this->summaryItem('Entradas', number_format((clone $query)->where('tipo', 'entrada')->count())),
                $this->summaryItem('Salidas', number_format((clone $query)->where('tipo', 'salida')->count())),
                $this->summaryItem('Ajustes', number_format((clone $query)->where('tipo', 'ajuste')->count())),
            ],
            'Kardex de movimientos de inventario registrados en el periodo, incluyendo compras, ventas, alquileres y ajustes.',
        ];
    }

    private function formatRow(string $type, mixed $record): array
    {
        return match ($type) {
            'ventas' => [
                $record->serie.'-'.str_pad((string) $record->numero, 6, '0', STR_PAD_LEFT),
                $record->cliente?->nombres ?? 'Cliente no disponible',
                $record->usuario?->nombre ?? 'Usuario no disponible',
                Carbon::parse($record->fecha_emision)->format('d/m/Y'),
                $this->paymentLabel($record->medio_pago),
                $this->money($record->total),
                $record->estado === 'anulada' ? 'Anulada' : 'Completada',
            ],
            'alquileres' => [
                'ALQ-'.$record->id_alquiler,
                $record->producto?->nombre ?? 'Equipo no disponible',
                $record->cliente?->nombres ?? 'Cliente no disponible',
                $record->usuario?->nombre ?? 'Usuario no disponible',
                Carbon::parse($record->fecha_entrega)->format('d/m/Y'),
                $this->paymentLabel($record->medio_pago),
                $this->money($record->total_alquiler),
                ucfirst($record->estado),
            ],
            'cobranzas' => [
                'PAG-'.$record->id_pago,
                $record->cobranza?->alquiler?->cliente?->nombres ?? 'Cliente no disponible',
                'ALQ-'.$record->cobranza?->id_alquiler,
                $record->usuario?->nombre ?? 'Usuario no disponible',
                $record->created_at?->format('d/m/Y H:i') ?? '',
                $this->paymentLabel($record->medio_pago),
                $this->money($record->monto),
                $record->observaciones ?? '—',
            ],
            'caja' => [
                ucfirst($record->tipo_movimiento),
                $record->caja?->tipo_caja === Caja::TIPO_ALQUILERES ? 'Alquileres/Cobranzas' : 'Venta de Productos',
                $record->categoria,
                $record->detalle,
                $record->created_at?->format('d/m/Y H:i') ?? '',
                $this->paymentLabel($record->medio_pago),
                $record->usuario?->nombre ?? 'Usuario no disponible',
                $this->money($record->monto),
            ],
            'inventario' => [
                $record->codigo,
                $record->nombre,
                $record->categoria?->nombre ?? '—',
                $record->marca?->nombre ?? '—',
                number_format((int) $record->stock),
                number_format((int) $record->stock_minimo),
                $this->money($record->precio_compra),
                $this->money((int) $record->stock * (float) $record->precio_compra),
            ],
            'compras' => [
                'COM-'.$record->id_compra,
                $record->proveedor?->razon_social ?? 'Proveedor no disponible',
                $record->proveedor?->ruc ?? '—',
                $record->usuario?->nombre ?? 'Usuario no disponible',
                Carbon::parse($record->fecha_compra)->format('d/m/Y'),
                trim($record->tipo_comprobante.' '.$record->numero_comprobante),
                $this->money($record->total),
                $this->money($record->pagos_sum_monto ?? 0),
                $this->money($record->estado === 'recibida'
                    ? max((float) $record->total - (float) ($record->pagos_sum_monto ?? 0), 0)
                    : 0),
                ucfirst($record->estado),
            ],
            'movimientos_inventario' => [
                $record->created_at?->format('d/m/Y H:i') ?? '',
                $record->producto?->codigo.' · '.($record->producto?->nombre ?? 'Producto no disponible'),
                ucfirst($record->tipo),
                number_format((int) $record->cantidad),
                number_format((int) $record->stock_resultante),
                $record->motivo,
                $record->referencia ?? '—',
                $record->usuario?->nombre ?? 'Usuario no disponible',
            ],
        };
    }

    /**
     * @return array{etiqueta: string, valor: string}
     */
    private function summaryItem(string $label, string $value): array
    {
        return ['etiqueta' => $label, 'valor' => $value];
    }

    private function money(float|int|string|null $amount): string
    {
        return 'S/ '.number_format((float) $amount, 2);
    }

    private function paymentLabel(?string $method): string
    {
        return match ($method) {
            'efectivo' => 'Efectivo',
            'yape' => 'Yape / Plin',
            'deposito' => 'Depósito / transferencia',
            default => ucfirst($method ?? 'No especificado'),
        };
    }

    private function authorizeAccess(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof Usuario && $user->esAdmin(), 403);
    }
}
