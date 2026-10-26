<?php

$c1a = <<<'HTML'
@extends('layouts.app')

@section('title', 'Gestión de Cobranzas y Moras - Ferretería Jack Sparkys')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-hand-holding-dollar text-amber-500"></i> Módulo de Cobranzas & Control de Morosos
            </h1>
            <p class="text-xs text-slate-400">Monitoreo de clientes con alquileres vencidos, recargo por mora y recuperación de herramientas en obra</p>
        </div>
        <div class="bg-rose-500/10 border border-rose-500/30 px-4 py-2 rounded-xl text-right">
            <span class="text-[10px] text-rose-300 font-bold uppercase block">Total Moras Pendientes</span>
            <span class="text-lg font-extrabold text-rose-400">S/ {{ number_format($totalPendiente, 2) }}</span>
        </div>
    </div>

    <!-- Buscador de Clientes Morosos -->
    <div class="bg-slate-800 p-4 rounded-2xl border border-slate-700">
        <form action="{{ route('cobranzas.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por cliente, DNI/RUC, dirección u obra..." class="w-full bg-slate-900 border border-slate-700 rounded-xl py-2.5 pl-9 pr-4 text-xs text-slate-200 focus:outline-none focus:border-amber-500">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
            </div>
            <select name="estado" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-amber-500">
                <option value="">Todos los Estados</option>
                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Con Mora Pendiente</option>
                <option value="pagado" {{ request('estado') == 'pagado' ? 'selected' : '' }}>Moras Pagadas</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs transition">
                Filtrar Registros
            </button>
        </form>
    </div>
HTML;

@mkdir(__DIR__ . '/resources/views/cobranzas', 0777, true);
file_put_contents(__DIR__ . '/resources/views/cobranzas/index.blade.php', $c1a);
echo "cv_a done\n";
