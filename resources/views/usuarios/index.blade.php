@extends('layouts.app')

@section('title', 'Usuarios - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Gestión de Usuarios</h1>
            <p class="text-xs text-slate-400">Administra las cuentas, roles y accesos del sistema.</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-2">
            <span class="block text-[10px] font-bold uppercase text-amber-300">Usuarios encontrados</span>
            <span class="text-lg font-extrabold text-amber-400">{{ $usuarios->total() }}</span>
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
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-100">
                        {{ $usuarioEditando ? 'Editar usuario' : 'Registrar usuario' }}
                    </h2>
                    <p class="mt-1 text-xs text-slate-400">La contraseña debe tener al menos 6 caracteres.</p>
                </div>
                @if ($usuarioEditando)
                    <a href="{{ route('usuarios.index', request()->query()) }}" class="text-xs font-semibold text-slate-400 hover:text-white">
                        Cancelar
                    </a>
                @endif
            </div>

            <form
                action="{{ $usuarioEditando ? route('usuarios.update', $usuarioEditando->id_usuario) : route('usuarios.store') }}"
                method="POST"
                class="space-y-4"
                autocomplete="off"
            >
                @csrf
                @if ($usuarioEditando)
                    @method('PUT')
                @endif

                <div>
                    <label for="nombre" class="mb-1 block text-xs font-semibold text-slate-300">Nombre completo</label>
                    <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $usuarioEditando?->nombre) }}" maxlength="100" required
                        autocomplete="name" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('nombre') ? 'true' : 'false' }}">
                    @error('nombre')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="usuario" class="mb-1 block text-xs font-semibold text-slate-300">Usuario</label>
                    <input id="usuario" name="usuario" type="text" value="{{ old('usuario', $usuarioEditando?->usuario) }}" maxlength="50" required
                        autocomplete="username" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('usuario') ? 'true' : 'false' }}">
                    @error('usuario')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-xs font-semibold text-slate-300">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $usuarioEditando?->email) }}" maxlength="100" required
                        autocomplete="email" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    @error('email')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="id_rol" class="mb-1 block text-xs font-semibold text-slate-300">Rol</label>
                    <select id="id_rol" name="id_rol" required
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('id_rol') ? 'true' : 'false' }}">
                        <option value="">Selecciona un rol</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}" @selected((string) old('id_rol', $usuarioEditando?->id_rol) === (string) $rol->id_rol)>
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_rol')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-xs font-semibold text-slate-300">
                        Contraseña {{ $usuarioEditando ? '(dejar en blanco para conservarla)' : '' }}
                    </label>
                    <input id="password" name="password" type="password" minlength="6" {{ $usuarioEditando ? '' : 'required' }}
                        autocomplete="new-password" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                    @error('password')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-xs font-semibold text-slate-300">Confirmar contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" minlength="6"
                        autocomplete="new-password" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                </div>

                <div>
                    <label for="sexo" class="mb-1 block text-xs font-semibold text-slate-300">Sexo</label>
                    <select id="sexo" name="sexo" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                        <option value="">No especificado</option>
                        <option value="M" @selected(old('sexo', $usuarioEditando?->sexo) === 'M')>Masculino</option>
                        <option value="F" @selected(old('sexo', $usuarioEditando?->sexo) === 'F')>Femenino</option>
                    </select>
                    @error('sexo')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="telefono" class="mb-1 block text-xs font-semibold text-slate-300">Teléfono</label>
                    <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $usuarioEditando?->telefono) }}" maxlength="20"
                        autocomplete="tel" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('telefono')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="direccion" class="mb-1 block text-xs font-semibold text-slate-300">Dirección</label>
                    <input id="direccion" name="direccion" type="text" value="{{ old('direccion', $usuarioEditando?->direccion) }}" maxlength="150"
                        autocomplete="street-address" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    @error('direccion')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="sobre_mi" class="mb-1 block text-xs font-semibold text-slate-300">Sobre mí</label>
                    <textarea id="sobre_mi" name="sobre_mi" rows="3" maxlength="1000"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">{{ old('sobre_mi', $usuarioEditando?->sobre_mi) }}</textarea>
                    @error('sobre_mi')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-amber-500 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                    {{ $usuarioEditando ? 'Guardar cambios' : 'Registrar usuario' }}
                </button>
            </form>
        </section>

        <section class="space-y-4 xl:col-span-2">
            <form method="GET" action="{{ route('usuarios.index') }}" class="flex flex-col gap-2 rounded-2xl border border-slate-700 bg-slate-800 p-4 sm:flex-row">
                <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, usuario o correo"
                    class="min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-500 focus:outline-none">
                <select name="estado" class="rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                    <option value="">Todos los estados</option>
                    <option value="activo" @selected(request('estado') === 'activo')>Activos</option>
                    <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivos</option>
                </select>
                <button type="submit" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-600">Buscar</button>
                @if (request()->hasAny(['buscar', 'estado']))
                    <a href="{{ route('usuarios.index') }}" class="rounded-lg px-3 py-2 text-center text-sm font-semibold text-slate-300 hover:bg-slate-700">Limpiar</a>
                @endif
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-900/70 text-xs uppercase text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Usuario</th>
                                <th class="px-4 py-3">Contacto</th>
                                <th class="px-4 py-3">Rol</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @forelse ($usuarios as $usuario)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-100">{{ $usuario->nombre }}</p>
                                        <p class="text-xs text-slate-400">{{ '@' . $usuario->usuario }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-slate-200">{{ $usuario->email }}</p>
                                        <p class="text-xs text-slate-400">{{ $usuario->telefono ?: 'Sin teléfono' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-300">{{ $usuario->rol?->nombre ?? 'Sin rol' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $usuario->activo ? 'bg-emerald-500/10 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">
                                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('usuarios.edit', ['usuario' => $usuario->id_usuario] + request()->query()) }}"
                                                class="rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:bg-slate-700">
                                                Editar
                                            </a>
                                            <form action="{{ route('usuarios.destroy', $usuario->id_usuario) }}" method="POST"
                                                onsubmit="return confirm('¿Confirmas {{ $usuario->activo ? 'dar de baja' : 'reactivar' }} este usuario?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ $usuario->activo ? 'border-rose-500/40 text-rose-300 hover:bg-rose-500/10' : 'border-emerald-500/40 text-emerald-300 hover:bg-emerald-500/10' }}">
                                                    {{ $usuario->activo ? 'Dar de baja' : 'Reactivar' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">No se encontraron usuarios.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($usuarios->hasPages())
                    <div class="border-t border-slate-700 px-4 py-3">{{ $usuarios->links() }}</div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
