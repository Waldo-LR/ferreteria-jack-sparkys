@extends('layouts.app')

@section('title', 'Proveedores - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Gestión de Proveedores</h1>
            <p class="text-xs text-slate-400">Directorio de proveedores y datos de contacto</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-2">
            <span class="block text-[10px] font-bold uppercase text-amber-300">Proveedores activos</span>
            <span class="text-lg font-extrabold text-amber-400">{{ $proveedores->total() }}</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300" role="alert">
            <p class="font-bold">Revisa los datos del formulario:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section class="h-fit rounded-2xl border border-slate-700 bg-slate-800 p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-100">
                        {{ $proveedorEditando ? 'Editar proveedor' : 'Registrar proveedor' }}
                    </h2>
                    <p class="mt-1 text-xs text-slate-400">El RUC debe tener 11 dígitos.</p>
                </div>
                @if ($proveedorEditando)
                    <a href="{{ route('proveedores.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white">
                        Cancelar
                    </a>
                @endif
            </div>

            <form
                action="{{ $proveedorEditando ? route('proveedores.update', $proveedorEditando->id_proveedor) : route('proveedores.store') }}"
                method="POST"
                class="space-y-4"
            >
                @csrf
                @if ($proveedorEditando)
                    @method('PUT')
                @endif

                <div>
                    <label for="razon_social" class="mb-1 block text-xs font-semibold text-slate-300">Razón social</label>
                    <input
                        id="razon_social"
                        type="text"
                        name="razon_social"
                        value="{{ old('razon_social', $proveedorEditando?->razon_social) }}"
                        maxlength="150"
                        required
                        autocomplete="organization"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('razon_social') ? 'true' : 'false' }}"
                    >
                    @error('razon_social')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ruc" class="mb-1 block text-xs font-semibold text-slate-300">RUC</label>
                    <input
                        id="ruc"
                        type="text"
                        name="ruc"
                        value="{{ old('ruc', $proveedorEditando?->ruc) }}"
                        inputmode="numeric"
                        pattern="[0-9]{11}"
                        maxlength="11"
                        minlength="11"
                        required
                        autocomplete="off"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('ruc') ? 'true' : 'false' }}"
                    >
                    @error('ruc')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telefono" class="mb-1 block text-xs font-semibold text-slate-300">Teléfono</label>
                    <input
                        id="telefono"
                        type="tel"
                        name="telefono"
                        value="{{ old('telefono', $proveedorEditando?->telefono) }}"
                        maxlength="20"
                        autocomplete="tel"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('telefono') ? 'true' : 'false' }}"
                    >
                    @error('telefono')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-xs font-semibold text-slate-300">Correo electrónico</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $proveedorEditando?->email) }}"
                        maxlength="100"
                        autocomplete="email"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="direccion" class="mb-1 block text-xs font-semibold text-slate-300">Dirección</label>
                    <textarea
                        id="direccion"
                        name="direccion"
                        rows="3"
                        maxlength="200"
                        autocomplete="street-address"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('direccion') ? 'true' : 'false' }}"
                    >{{ old('direccion', $proveedorEditando?->direccion) }}</textarea>
                    @error('direccion')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-amber-500 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                    {{ $proveedorEditando ? 'Guardar cambios' : 'Registrar proveedor' }}
                </button>
            </form>
        </section>

        <section class="space-y-4 xl:col-span-2">
            <form action="{{ route('proveedores.index') }}" method="GET" class="flex flex-col gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4 sm:flex-row">
                <label for="buscar" class="sr-only">Buscar proveedor</label>
                <input
                    id="buscar"
                    type="search"
                    name="buscar"
                    value="{{ request('buscar') }}"
                    placeholder="Buscar por razón social, RUC, correo o teléfono..."
                    class="min-w-0 flex-1 rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-xs text-slate-100 focus:border-amber-500 focus:outline-none"
                >
                <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-bold text-slate-950 transition hover:bg-amber-400">
                    Buscar
                </button>
                @if (request()->filled('buscar'))
                    <a href="{{ route('proveedores.index') }}" class="rounded-xl border border-slate-600 px-4 py-2.5 text-center text-xs font-semibold text-slate-300 hover:bg-slate-700">
                        Limpiar
                    </a>
                @endif
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-900/80 text-xs uppercase text-slate-400">
                            <tr>
                                <th class="p-4">Proveedor</th>
                                <th class="p-4">RUC</th>
                                <th class="p-4">Contacto</th>
                                <th class="p-4">Dirección</th>
                                <th class="p-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @forelse ($proveedores as $proveedor)
                                <tr class="align-top transition hover:bg-slate-700/40">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-100">{{ $proveedor->razon_social }}</div>
                                        <div class="mt-1 text-[11px] text-slate-500">#{{ $proveedor->id_proveedor }}</div>
                                    </td>
                                    <td class="p-4 font-semibold text-amber-400">{{ $proveedor->ruc }}</td>
                                    <td class="p-4 text-xs text-slate-300">
                                        <div>{{ $proveedor->telefono ?: 'Sin teléfono' }}</div>
                                        <div class="mt-1 break-all text-slate-400">{{ $proveedor->email ?: 'Sin correo' }}</div>
                                    </td>
                                    <td class="max-w-xs whitespace-normal p-4 text-xs text-slate-400">
                                        {{ $proveedor->direccion ?: 'Sin dirección registrada' }}
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-center gap-2">
                                            <a
                                                href="{{ route('proveedores.edit', $proveedor->id_proveedor) }}"
                                                class="rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-200 transition hover:border-amber-500 hover:text-amber-400"
                                            >
                                                Editar
                                            </a>
                                            <form
                                                action="{{ route('proveedores.destroy', $proveedor->id_proveedor) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Dar de baja a este proveedor?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-rose-500/30 px-3 py-1.5 text-xs font-semibold text-rose-400 transition hover:bg-rose-500 hover:text-white">
                                                    Dar de baja
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-sm text-slate-400">
                                        @if (request()->filled('buscar'))
                                            No se encontraron proveedores para “{{ request('buscar') }}”.
                                        @else
                                            Aún no hay proveedores registrados.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($proveedores->hasPages())
                <div class="rounded-xl border border-slate-700 bg-slate-800 p-3">
                    {{ $proveedores->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
