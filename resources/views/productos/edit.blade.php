@extends('layouts.app')

@section('title', 'Editar producto - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-100">Editar producto</h1>
            <p class="mt-1 text-sm text-slate-400">{{ $producto->nombre }} · {{ $producto->codigo }}</p>
        </div>
        <a href="{{ route('productos.index') }}" class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-600 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-500 hover:text-white sm:self-auto">
            <i class="fa-solid fa-arrow-left"></i>
            Volver al catálogo
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-200" role="alert">
            <p class="font-bold">Revisa la información ingresada:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data" class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Código *</span>
                <input type="text" name="codigo" value="{{ old('codigo', $producto->codigo) }}" required maxlength="50" autocomplete="off" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
            </label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Nombre *</span>
                <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}" required maxlength="150" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
            </label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Categoría *</span>
                <select name="id_categoria" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                    <option value="">Selecciona una categoría</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id_categoria }}" @selected(old('id_categoria', $producto->id_categoria) == $categoria->id_categoria)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Marca *</span>
                <select name="id_marca" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                    <option value="">Selecciona una marca</option>
                    @foreach($marcas as $marca)
                        <option value="{{ $marca->id_marca }}" @selected(old('id_marca', $producto->id_marca) == $marca->id_marca)>{{ $marca->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Stock actual</span>
                <input type="number" value="{{ $producto->stock }}" disabled class="w-full cursor-not-allowed rounded-xl border border-slate-700 bg-slate-900/60 px-3 py-2.5 text-sm text-slate-400">
                <span class="block font-normal text-slate-500">El stock se modifica desde Control de Inventario.</span>
            </label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Stock mínimo *</span>
                <input type="number" name="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo) }}" required min="0" step="1" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
            </label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Costo unitario (S/) *</span>
                <input type="number" name="precio_compra" value="{{ old('precio_compra', $producto->precio_compra) }}" required min="0" step="0.01" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
            </label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Precio de venta (S/) *</span>
                <input type="number" name="precio_venta" value="{{ old('precio_venta', $producto->precio_venta) }}" required min="0" step="0.01" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
            </label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Precio mayorista (S/)</span>
                <input type="number" name="precio_venta_mayor" value="{{ old('precio_venta_mayor', $producto->precio_venta_mayor) }}" min="0" step="0.01" placeholder="Igual al precio de venta" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
            </label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Cantidad mínima mayorista</span>
                <input type="number" name="cantidad_mayorista" value="{{ old('cantidad_mayorista', $producto->cantidad_mayorista) }}" min="1" step="1" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
            </label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300 sm:col-span-2 xl:col-span-3">
<span>Imagen del producto</span> @if($producto->imagen) <img src="{{ asset('storage/' . ltrim($producto->imagen, '/')) }}" alt="Imagen actual de {{ $producto->nombre }}" class="mb-2 h-20 w-20 rounded-xl border border-slate-600 object-cover"> @endif <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-1.5 file:font-bold file:text-slate-950"> <span class="block font-normal text-slate-500">JPG, PNG o WebP. Máximo 2 MB.</span> </label> <label class="space-y-1.5 text-xs font-semibold text-slate-300 sm:col-span-2 xl:col-span-3"> <span>Descripción</span>
                <textarea name="descripcion" rows="3" maxlength="5000" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">{{ old('descripcion', $producto->descripcion) }}</textarea>
            </label>
            <div class="flex flex-col gap-4 sm:col-span-2 sm:flex-row sm:items-center xl:col-span-3">
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="es_alquilable" value="1" @checked(old('es_alquilable', $producto->es_alquilable)) class="rounded border-slate-600 bg-slate-900 text-amber-500 focus:ring-amber-500">
                    También se alquila
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-300">
                    <span>Precio alquiler/día (S/)</span>
                    <input type="number" name="precio_alquiler_dia" value="{{ old('precio_alquiler_dia', $producto->precio_alquiler_dia) }}" min="0" step="0.01" class="w-36 rounded-xl border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                </label>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400 sm:ml-auto">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar cambios
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
