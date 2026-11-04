<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante {{ $venta->serie }}-{{ str_pad($venta->numero, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page { margin: 18mm; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 20px; background: #f8fafc; color: #111827; }
        .ticket { max-width: 380px; margin: 0 auto; background: #fff; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .table th, .table td { text-align: left; padding: 4px 0; }
        .receipt-actions { display: flex; gap: 8px; margin-top: 15px; }
        .receipt-actions a, .receipt-actions button { flex: 1; padding: 10px; text-align: center; text-decoration: none; font: bold 12px Arial, sans-serif; border: none; border-radius: 6px; cursor: pointer; }
        .btn-print { background: #f59e0b; color: #000; }
        .btn-pdf { background: #0f172a; color: #fff; }
        @media print { .receipt-actions { display: none !important; } body { background: none; padding: 0; } .ticket { border: none; box-shadow: none; width: 100%; max-width: none; padding: 0; } }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="text-center">
            <h2 style="margin: 0;">{{ $config->razon_social ?? "FERRETERÍA JACK SPARKY'S" }}</h2>
            <p style="margin: 4px 0;">RUC: {{ $config->ruc ?? '20609876543' }}</p>
            <p style="margin: 2px 0; font-size: 11px;">{{ $config->direccion ?? 'Av. Los Héroes 123, Lima' }}</p>
            <p style="margin: 2px 0; font-size: 11px;">Telf: {{ $config->telefono ?? '987654321' }}</p>
        </div>

        <div class="divider"></div>

        <div class="text-center bold">
            <p style="margin: 2px 0; font-size: 14px; text-transform: uppercase;">
                {{ str_replace('_', ' ', $venta->tipo_comprobante) }}
            </p>
            <p style="margin: 2px 0; font-size: 16px;">{{ $venta->serie }}-{{ str_pad($venta->numero, 6, '0', STR_PAD_LEFT) }}</p>
        </div>

        <div class="divider"></div>

        <div>
            <p style="margin: 2px 0;"><strong>Fecha:</strong> {{ $venta->fecha_emision }}</p>
            <p style="margin: 2px 0;"><strong>Cliente:</strong> {{ $venta->cliente->nombres ?? 'Cliente General' }}</p>
            <p style="margin: 2px 0;"><strong>DNI/RUC:</strong> {{ $venta->cliente->dni_ruc ?? '00000000' }}</p>
            <p style="margin: 2px 0;"><strong>Medio Pago:</strong> {{ strtoupper($venta->medio_pago) }}</p>
            @if ($venta->usuario)
                <p style="margin: 2px 0;"><strong>Atendido por:</strong> {{ $venta->usuario->nombre }}</p>
            @endif
            @if ($venta->estado === 'anulada')
                <p style="margin: 6px 0; color: #b91c1c; font-weight: bold;">COMPROBANTE ANULADO</p>
                @if ($venta->motivo_anulacion)
                    <p style="margin: 2px 0;"><strong>Motivo:</strong> {{ $venta->motivo_anulacion }}</p>
                @endif
            @endif
        </div>

        <div class="divider"></div>

        <table class="table">
            <thead>
                <tr>
                    <th>Cant</th>
                    <th>Producto</th>
                    <th class="text-right">P.U</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $det)
                    <tr>
                        <td>{{ $det->cantidad }}</td>
                        <td>{{ $det->producto->nombre ?? 'Art.' }}</td>
                        <td class="text-right">{{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="text-right">{{ number_format($det->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <table class="table">
            @if($venta->tipo_comprobante === 'factura')
                <tr>
                    <td>Op. Gravada:</td>
                    <td class="text-right">S/ {{ number_format($venta->valor_venta, 2) }}</td>
                </tr>
                <tr>
                    <td>IGV (18%):</td>
                    <td class="text-right">S/ {{ number_format($venta->igv, 2) }}</td>
                </tr>
            @endif
            <tr class="bold" style="font-size: 14px;">
                <td>TOTAL FINAL:</td>
                <td class="text-right">S/ {{ number_format($venta->total, 2) }}</td>
            </tr>
            <tr>
                <td>Monto Pagado:</td>
                <td class="text-right">S/ {{ number_format($venta->monto_pagado, 2) }}</td>
            </tr>
            <tr>
                <td>Vuelto / Cambio:</td>
                <td class="text-right">S/ {{ number_format($venta->vuelto, 2) }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="text-center" style="font-size: 11px;">
            <p style="margin: 4px 0;">Representación Impresa de Comprobante de Venta</p>
            <p style="margin: 2px 0; font-style: italic;">"{{ $config->slogan ?? 'Tu mejor aliado, en tus proyectos' }}"</p>
            <p style="margin: 6px 0;" class="bold">¡Gracias por su preferencia!</p>
        </div>

        @unless ($pdf ?? false)
            <div class="receipt-actions">
                <button type="button" onclick="window.print()" class="btn-print">IMPRIMIR COMPROBANTE</button>
                <a href="{{ route('ventas.pdf', $venta->id_venta) }}" class="btn-pdf">DESCARGAR PDF</a>
            </div>
        @endunless
    </div>
    @if (($autoPrint ?? false) && ! ($pdf ?? false))
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
</body>
</html>
