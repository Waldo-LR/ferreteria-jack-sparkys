<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['titulo'] }}</title>
    <style>
        @page { margin: 28px 30px; }
        body { color: #1e293b; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        .header { border-bottom: 2px solid #d97706; margin-bottom: 16px; padding-bottom: 12px; }
        .business { color: #64748b; font-size: 9px; margin-bottom: 4px; }
        h1 { color: #0f172a; font-size: 18px; margin: 0 0 5px; }
        .meta { color: #64748b; font-size: 9px; }
        .summary { border-collapse: separate; border-spacing: 8px 0; margin: 0 -8px 16px; width: calc(100% + 16px); }
        .summary td { background: #f1f5f9; border-radius: 6px; padding: 9px 11px; width: {{ 100 / max(count($report['resumen']), 1) }}%; }
        .summary-label { color: #64748b; display: block; font-size: 8px; margin-bottom: 5px; }
        .summary-value { color: #0f172a; font-size: 13px; font-weight: bold; }
        .note { color: #64748b; font-size: 8px; margin: 0 0 10px; }
        table.data { border-collapse: collapse; width: 100%; }
        .data th { background: #0f172a; color: #fff; font-size: 7px; padding: 8px 6px; text-align: left; text-transform: uppercase; }
        .data td { border-bottom: 1px solid #e2e8f0; font-size: 8px; padding: 7px 6px; vertical-align: top; }
        .data tr:nth-child(even) td { background: #f8fafc; }
        .empty { color: #64748b; padding: 20px; text-align: center; }
        .footer { border-top: 1px solid #e2e8f0; color: #64748b; font-size: 8px; margin-top: 14px; padding-top: 7px; }
    </style>
</head>
<body>
    <div class="header">
        @if($configuracion)
            <div class="business">{{ $configuracion->razon_social }}{{ $configuracion->ruc ? ' · RUC '.$configuracion->ruc : '' }}</div>
        @endif
        <h1>{{ $report['titulo'] }}</h1>
        <div class="meta">
            @if($filters['tipo'] === 'inventario')
                Inventario actual
            @else
                Periodo: {{ \Carbon\Carbon::parse($filters['fecha_desde'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($filters['fecha_hasta'])->format('d/m/Y') }}
            @endif
            · Generado: {{ $generatedAt->format('d/m/Y H:i') }}
        </div>
    </div>

    <table class="summary">
        <tr>
            @foreach($report['resumen'] as $item)
                <td>
                    <span class="summary-label">{{ $item['etiqueta'] }}</span>
                    <span class="summary-value">{{ $item['valor'] }}</span>
                </td>
            @endforeach
        </tr>
    </table>

    <p class="note">{{ $report['nota'] }}</p>

    <table class="data">
        <thead>
            <tr>
                @foreach($report['columnas'] as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($report['filas'] as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="{{ count($report['columnas']) }}">No hay registros para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Documento generado por el sistema de gestión · {{ number_format($report['filas']->count()) }} registros</div>
</body>
</html>
