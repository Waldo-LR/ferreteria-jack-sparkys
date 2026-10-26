<?php

$p1 = <<<'HTML'
@extends('layouts.app')

@section('title', 'Registrar Venta - POS - Ferretería Jack Sparkys')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-cart-shopping text-amber-500"></i> Módulo Registrar Venta (POS)
            </h1>
            <p class="text-xs text-slate-400">Emisión de Boletas, Facturas y Notas de Venta / Proformas en tiempo real (IGV 18%)</p>
        </div>
        <div class="bg-slate-800 border border-slate-700 px-4 py-2 rounded-xl text-right">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">N° Operación Interna</span>
            <span class="text-sm font-extrabold text-amber-400">{{ $nroVenta }}</span>
        </div>
    </div>

    <!-- Formulario POS de 3 Paneles -->
    <form id="formPOS" action="{{ route('ventas.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- PANEL 1: DETALLE DE PRODUCTOS (2 Columnas) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center">1</span>
                            <h2 class="font-bold text-slate-200 text-sm">Detalle de Productos a Vender</h2>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5 text-xs text-slate-300">
                                <i class="fa-solid fa-calendar text-amber-400"></i>
                                <span>Emisión:</span>
                                <input type="date" name="fecha_emision" value="{{ date('Y-m-d') }}" class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-1 focus:outline-none focus:border-amber-500">
                            </div>
                            <button type="button" onclick="abrirModalBuscarProductos()" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs transition flex items-center gap-1.5 shadow">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <span>Buscar Productos</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs" id="tablaPOS">
                            <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px]">
                                <tr>
                                    <th class="p-3">N°</th>
                                    <th class="p-3">Producto</th>
                                    <th class="p-3">Detalle / Marca</th>
                                    <th class="p-3 text-center w-24">Cantidad</th>
                                    <th class="p-3 text-right">Precio Unit.</th>
                                    <th class="p-3 text-right">Sub Total</th>
                                    <th class="p-3 text-center w-16">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/60" id="bodyPOS">
                                <tr id="rowVacia">
                                    <td colspan="7" class="p-8 text-center text-slate-500 italic">
                                        <i class="fa-solid fa-basket-shopping text-3xl mb-2 block text-slate-600"></i>
                                        Aún no has agregado productos a la venta. Haz clic en <strong>"Buscar Productos"</strong> para seleccionar artículos del catálogo.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-900/90 text-slate-200 border-t-2 border-slate-700 font-bold">
                                <tr>
                                    <td colspan="5" class="p-3 text-right text-xs uppercase tracking-wider">Importe Total General:</td>
                                    <td class="p-3 text-right text-base text-emerald-400 font-extrabold" id="totalGeneralDisplay">S/ 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
HTML;

@mkdir(__DIR__ . '/resources/views/ventas', 0777, true);
file_put_contents(__DIR__ . '/resources/views/ventas/pos.blade.php', $p1);
echo "pos p1 done\n";
