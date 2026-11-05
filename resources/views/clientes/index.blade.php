@extends('layouts.app')

@section('title', 'Gestión de Clientes - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Gestión de Clientes</h1>
            <p class="text-xs text-slate-400">Registra y consulta los datos de contacto de personas y empresas.</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-2">
            <span class="block text-[10px] font-bold uppercase text-amber-300">Clientes encontrados</span>
            <span class="text-lg font-extrabold text-amber-400">{{ number_format($clientes->total()) }}</span>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-300" role="status">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300" role="alert">
            {{ session('error') }}
        </div>
    @endif
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

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="h-fit rounded-2xl border border-slate-700 bg-slate-800 p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <h2 class="text-sm font-bold text-slate-100">{{ $clienteEditando ? 'Editar cliente' : 'Registrar cliente' }}</h2>
                @if ($clienteEditando)
                    <a href="{{ route('clientes.index', request()->query()) }}" class="text-xs font-semibold text-slate-400 hover:text-white">Cancelar</a>
                @endif
            </div>

            <form action="{{ $clienteEditando ? route('clientes.update', $clienteEditando->id_cliente) : route('clientes.store') }}" method="POST" class="space-y-4" autocomplete="on">
                @csrf
                @if ($clienteEditando) @method('PUT') @endif

                <div>
                    <label for="tipo_documento" class="mb-1 block text-xs font-semibold text-slate-300">Tipo de documento</label>
                    <select id="tipo_documento" name="tipo_documento" required
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('tipo_documento') ? 'true' : 'false' }}">
                        <option value="">Selecciona un documento</option>
                        @foreach (['DNI', 'RUC', 'CE'] as $tipoDocumento)
                            <option value="{{ $tipoDocumento }}" @selected(old('tipo_documento', $clienteEditando?->tipo_documento) === $tipoDocumento)>{{ $tipoDocumento }}</option>
                        @endforeach
                    </select>
                    @error('tipo_documento')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="dni_ruc" class="mb-1 block text-xs font-semibold text-slate-300">Número de documento</label>
                    <input id="dni_ruc" name="dni_ruc" type="text" value="{{ old('dni_ruc', $clienteEditando?->dni_ruc) }}" maxlength="20" required
                        autocomplete="off" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('dni_ruc') ? 'true' : 'false' }}">
                    @error('dni_ruc')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="nombres" class="mb-1 block text-xs font-semibold text-slate-300">Nombres / Razón social</label>
                    <input id="nombres" name="nombres" type="text" value="{{ old('nombres', $clienteEditando?->nombres) }}" maxlength="150" required
                        autocomplete="name" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('nombres') ? 'true' : 'false' }}">
                    @error('nombres')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-xs font-semibold text-slate-300">Correo electrónico <span class="font-normal text-slate-500">(opcional)</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email', $clienteEditando?->email) }}" maxlength="100"
                        autocomplete="email" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    @error('email')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="telefono" class="mb-1 block text-xs font-semibold text-slate-300">Teléfono <span class="font-normal text-slate-500">(opcional)</span></label>
                    <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $clienteEditando?->telefono) }}" maxlength="20"
                        autocomplete="tel" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('telefono') ? 'true' : 'false' }}">
                    @error('telefono')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="direccion" class="mb-1 block text-xs font-semibold text-slate-300">Dirección <span class="font-normal text-slate-500">(opcional)</span></label>
                    <input id="direccion" name="direccion" type="text" value="{{ old('direccion', $clienteEditando?->direccion) }}" maxlength="200"
                        autocomplete="street-address" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('direccion') ? 'true' : 'false' }}">
                    @error('direccion')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-amber-500 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                    {{ $clienteEditando ? 'Guardar cambios' : 'Registrar cliente' }}
                </button>
            </form>
        </section>

        <section class="min-w-0 space-y-4 xl:col-span-2">
            <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-col gap-2 rounded-2xl border border-slate-700 bg-slate-800 p-4 sm:flex-row">
                <label for="buscar_cliente" class="sr-only">Buscar cliente</label>
                <input id="buscar_cliente" type="search" name="buscar" value="{{ request('buscar') }}" maxlength="100"
                    placeholder="Buscar por nombre, documento, correo o teléfono"
                    class="min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-500 focus:outline-none">
                <button type="submit" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-600">Buscar</button>
                @if (request()->filled('buscar'))
                    <a href="{{ route('clientes.index') }}" class="rounded-lg px-3 py-2 text-center text-sm font-semibold text-slate-300 transition hover:bg-slate-700">Limpiar</a>
                @endif
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-900/70 text-xs uppercase text-slate-400">
                            <tr>
                                <th scope="col" class="px-4 py-3">Documento</th>
                                <th scope="col" class="px-4 py-3">Nombres / Razón social</th>
                                <th scope="col" class="px-4 py-3">Contacto</th>
                                <th scope="col" class="px-4 py-3">Dirección</th>
                                <th scope="col" class="px-4 py-3">Estado</th>
                                <th scope="col" class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @forelse ($clientes as $cliente)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-amber-400">{{ $cliente->dni_ruc }}</p>
                                        <p class="text-xs text-slate-400">{{ $cliente->tipo_documento }}</p>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-100">{{ $cliente->nombres }}</td>
                                    <td class="px-4 py-3">
                                        <p class="text-slate-200">{{ $cliente->telefono ?: 'Sin teléfono' }}</p>
                                        <p class="text-xs text-slate-400">{{ $cliente->email ?: 'Sin correo' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-300">{{ $cliente->direccion ?: 'Sin dirección' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $cliente->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                            {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('clientes.edit', [$cliente->id_cliente] + request()->query()) }}" class="mr-2 inline-flex rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-200 transition hover:bg-slate-700">
                                            Editar
                                        </a>
                                        <form class="inline" action="{{ route('clientes.destroy', $cliente->id_cliente) }}" method="POST"
                                            onsubmit="return confirm('¿Confirmas dar de baja a este cliente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-500/40 px-3 py-1.5 text-xs font-semibold text-rose-300 transition hover:bg-rose-500/10">
                                                {{ $cliente->activo ? 'Dar de baja' : 'Reactivar' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">No se encontraron clientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($clientes->hasPages())
                    <div class="border-t border-slate-700 px-4 py-3">{{ $clientes->links() }}</div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
