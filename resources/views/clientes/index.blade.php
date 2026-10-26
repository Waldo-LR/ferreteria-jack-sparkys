@extends('layouts.app')

@section('title', 'Clientes - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Gestión de Clientes</h1>
            <p class="text-xs text-slate-400">Directorio de personas y empresas clientes</p>
        </div>
    </div>

    <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900/80 text-slate-400 uppercase text-xs">
                <tr>
                    <th class="p-4">Documento</th>
                    <th class="p-4">Nombres / Razón Social</th>
                    <th class="p-4">Teléfono</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Dirección</th>
                    <th class="p-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($clientes as $cli)
                    <tr class="hover:bg-slate-700/40">
                        <td class="p-4 font-bold text-amber-400">
                            <span class="text-xs text-slate-400 block">{{ $cli->tipo_documento }}</span>
                            {{ $cli->dni_ruc }}
                        </td>
                        <td class="p-4 font-bold text-slate-200">{{ $cli->nombres }}</td>
                        <td class="p-4 text-slate-300">{{ $cli->telefono ?? '-' }}</td>
                        <td class="p-4 text-slate-300">{{ $cli->email ?? '-' }}</td>
                        <td class="p-4 text-slate-400 text-xs">{{ $cli->direccion ?? '-' }}</td>
                        <td class="p-4 text-center">
                            <form action="{{ route('clientes.destroy', $cli->id_cliente) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-400 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-slate-400">No hay clientes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
