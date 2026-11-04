@extends('layouts.app')

@section('title', 'Configuración de Empresa - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="mx-auto w-full max-w-5xl space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-100">Configuración de Empresa</h1>
        <p class="text-xs text-slate-400">Actualiza los datos comerciales y parámetros utilizados por el sistema.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300" role="alert">
            <p class="font-bold">No se guardaron los cambios. Revisa los siguientes campos:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('configuracion.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="space-y-5 rounded-2xl border border-slate-700 bg-slate-800 p-5 sm:p-6">
            <div>
                <h2 class="text-sm font-bold text-slate-100">Datos de la empresa</h2>
                <p class="mt-1 text-xs text-slate-400">Información que identifica al negocio en comprobantes y reportes.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="razon_social" class="mb-1 block text-xs font-semibold text-slate-300">Razón social</label>
                    <input id="razon_social" name="razon_social" type="text" value="{{ old('razon_social', $configuracion->razon_social) }}" maxlength="150" required
                        autocomplete="organization" aria-invalid="{{ $errors->has('razon_social') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('razon_social')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="ruc" class="mb-1 block text-xs font-semibold text-slate-300">RUC</label>
                    <input id="ruc" name="ruc" type="text" value="{{ old('ruc', $configuracion->ruc) }}" inputmode="numeric" pattern="[0-9]{11}" minlength="11" maxlength="11" required
                        aria-invalid="{{ $errors->has('ruc') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('ruc')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-xs font-semibold text-slate-300">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $configuracion->email) }}" maxlength="100" required
                        autocomplete="email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('email')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="telefono" class="mb-1 block text-xs font-semibold text-slate-300">Teléfono</label>
                    <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $configuracion->telefono) }}" maxlength="20" required
                        autocomplete="tel" aria-invalid="{{ $errors->has('telefono') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('telefono')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="direccion" class="mb-1 block text-xs font-semibold text-slate-300">Dirección</label>
                    <input id="direccion" name="direccion" type="text" value="{{ old('direccion', $configuracion->direccion) }}" maxlength="200" required
                        autocomplete="street-address" aria-invalid="{{ $errors->has('direccion') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('direccion')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="slogan" class="mb-1 block text-xs font-semibold text-slate-300">Eslogan</label>
                    <input id="slogan" name="slogan" type="text" value="{{ old('slogan', $configuracion->slogan) }}" maxlength="200"
                        aria-invalid="{{ $errors->has('slogan') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('slogan')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="space-y-5 rounded-2xl border border-slate-700 bg-slate-800 p-5 sm:p-6">
            <div>
                <h2 class="text-sm font-bold text-slate-100">Parámetros del negocio</h2>
                <p class="mt-1 text-xs text-slate-400">Valores utilizados en cálculos y avisos del sistema.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="igv_porcentaje" class="mb-1 block text-xs font-semibold text-slate-300">IGV (%)</label>
                    <input id="igv_porcentaje" name="igv_porcentaje" type="number" value="{{ old('igv_porcentaje', $configuracion->igv_porcentaje) }}" min="0" max="100" step="0.01" required
                        aria-invalid="{{ $errors->has('igv_porcentaje') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('igv_porcentaje')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mora_porcentaje" class="mb-1 block text-xs font-semibold text-slate-300">Mora (%)</label>
                    <input id="mora_porcentaje" name="mora_porcentaje" type="number" value="{{ old('mora_porcentaje', $configuracion->mora_porcentaje) }}" min="0" max="100" step="0.01" required
                        aria-invalid="{{ $errors->has('mora_porcentaje') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('mora_porcentaje')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="dias_recuperacion" class="mb-1 block text-xs font-semibold text-slate-300">Días para recuperación</label>
                    <input id="dias_recuperacion" name="dias_recuperacion" type="number" value="{{ old('dias_recuperacion', $configuracion->dias_recuperacion) }}" min="0" step="1" required
                        aria-invalid="{{ $errors->has('dias_recuperacion') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('dias_recuperacion')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="dias_alerta_vencimiento" class="mb-1 block text-xs font-semibold text-slate-300">Días de aviso de vencimiento</label>
                    <input id="dias_alerta_vencimiento" name="dias_alerta_vencimiento" type="number" value="{{ old('dias_alerta_vencimiento', $configuracion->dias_alerta_vencimiento) }}" min="0" step="1" required
                        aria-invalid="{{ $errors->has('dias_alerta_vencimiento') ? 'true' : 'false' }}"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('dias_alerta_vencimiento')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3 rounded-xl border border-slate-700 bg-slate-900/60 p-4 sm:col-span-2">
                    <input type="hidden" name="cierre_automatico" value="0">
                    <input id="cierre_automatico" name="cierre_automatico" type="checkbox" value="1"
                        @checked((bool) old('cierre_automatico', $configuracion->cierre_automatico))
                        class="size-4 rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500">
                    <label for="cierre_automatico" class="text-sm font-semibold text-slate-200">Habilitar cierre automático de caja</label>
                    @error('cierre_automatico')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                Guardar configuración
            </button>
        </div>
    </form>
</div>
@endsection
