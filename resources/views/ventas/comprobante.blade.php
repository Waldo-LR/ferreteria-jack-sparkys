<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante {{ $venta->serie }}-{{ str_pad($venta->numero, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 20px; background: #f8fafc; }
        .ticket { max-width: 380px; margin: 0 auto; background: #fff; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .table th, .table td { text-align: left; padding: 4px 0; }
        .btn-print { display: block; width: 100%; padding: 10px; background: #f59e0b; color: #000; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; margin-top: 15px; }
        @media print { .btn-print { display: none; } body { background: none; padding: 0; } .ticket { border: none; box-shadow: none; width: 100%; } }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="text-center">
            <h2 style="margin: 0;">FERRETERÍA JACK SPARKY'S</h2>
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

        <button onclick="window.print()" class="btn-print">🖨️ IMPRIMIR COMPROBANTE</button>
    </div>
</body>
</html>
