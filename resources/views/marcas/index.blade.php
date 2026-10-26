@extends('layouts.app')

@section('title', 'Marcas de Productos - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Marcas Registradas</h1>
            <p class="text-xs text-slate-400">Fabricantes y proveedores de herramientas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 h-fit">
            <h3 class="font-bold text-slate-200 mb-3 text-sm">Nueva Marca</h3>
            <form action="{{ route('marcas.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Nombre</label>
                    <input type="text" name="nombre" required placeholder="Ej: Truper" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                </div>
                <button type="submit" class="w-full bg-amber-500 text-slate-950 font-bold py-2 rounded-lg text-sm hover:bg-amber-400 transition">
                    Guardar Marca
                </button>
            </form>
        </div>

        <div class="md:col-span-2 bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-xs">
                    <tr>
                        <th class="p-4">ID</th>
                        <th class="p-4">Nombre de Marca</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($marcas as $m)
                        <tr class="hover:bg-slate-700/40">
                            <td class="p-4 font-bold text-amber-400">#{{ $m->id_marca }}</td>
                            <td class="p-4 font-semibold text-slate-200">{{ $m->nombre }}</td>
                            <td class="p-4 text-center">
                                <form action="{{ route('marcas.destroy', $m->id_marca) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-400 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-4 text-center text-slate-400">Sin marcas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
