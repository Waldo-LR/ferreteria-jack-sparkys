@extends('layouts.app')
@section('title', 'Añadir producto - Ferretería Jack Sparkys S.A.C.')
@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div><h1 class="text-2xl font-black text-slate-100">Añadir producto</h1><p class="mt-1 text-sm text-slate-400">Registra el producto y su imagen para identificarlo rápidamente.</p></div>
    @if($errors->any()) <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-200"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data" class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        @csrf
        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
            <label class="space-y-1.5 text-xs font-semibold text-slate-300"><span>Código *</span><input name="codigo" value="{{ old('codigo') }}" required maxlength="50" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100"></label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300"><span>Nombre *</span><input name="nombre" value="{{ old('nombre') }}" required maxlength="150" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100"></label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300"><span>Categoría *</span><select name="id_categoria" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100"><option value="">Selecciona</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id_categoria }}">{{ $categoria->nombre }}</option>@endforeach</select></label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300"><span>Marca *</span><select name="id_marca" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100"><option value="">Selecciona</option>@foreach($marcas as $marca)<option value="{{ $marca->id_marca }}">{{ $marca->nombre }}</option>@endforeach</select></label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300"><span>Stock inicial *</span><input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100"></label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300"><span>Stock mínimo *</span><input type="number" name="stock_minimo" value="{{ old('stock_minimo', 0) }}" min="0" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100"></label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300"><span>Costo unitario (S/) *</span><input type="number" name="precio_compra" value="{{ old('precio_compra') }}" min="0" step="0.01" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100"></label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300"><span>Precio de venta (S/) *</span><input type="number" name="precio_venta" value="{{ old('precio_venta') }}" min="0" step="0.01" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100"></label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300"><span>Imagen del producto</span><input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-1.5 file:font-bold file:text-slate-950"><span class="block font-normal text-slate-500">JPG, PNG o WebP. Máximo 2 MB.</span></label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300 sm:col-span-2 xl:col-span-3"><span>Descripción</span><textarea name="descripcion" rows="3" maxlength="5000" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">{{ old('descripcion') }}</textarea></label>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-700 p-5"><a href="{{ route('productos.index') }}" class="rounded-xl border border-slate-600 px-4 py-2.5 text-sm text-slate-300">Cancelar</a><button class="rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-slate-950">Guardar producto</button></div>
    </form>
</div>
@endsection
