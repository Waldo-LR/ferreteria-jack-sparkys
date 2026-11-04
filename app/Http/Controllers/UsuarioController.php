<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdministrator();

        return $this->renderIndex($request);
    }

    public function edit(Request $request, int $usuario)
    {
        $this->ensureAdministrator();

        $usuarioEditando = Usuario::findOrFail($usuario);

        return $this->renderIndex($request, $usuarioEditando);
    }

    private function renderIndex(Request $request, ?Usuario $usuarioEditando = null)
    {
        $validated = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(['activo', 'inactivo'])],
        ]);

        $query = Usuario::with('rol')->latest('id_usuario');

        if ($request->filled('buscar')) {
            $buscar = trim($validated['buscar']);
            $query->where(function ($query) use ($buscar) {
                $query->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('usuario', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        if (($validated['estado'] ?? null) === 'activo') {
            $query->where('activo', true);
        } elseif (($validated['estado'] ?? null) === 'inactivo') {
            $query->where('activo', false);
        }

        $usuarios = $query->paginate(15)->withQueryString();
        $roles = Rol::where('activo', true)->orderBy('nombre')->get();

        return view('usuarios.index', compact('usuarios', 'roles', 'usuarioEditando'));
    }

    public function store(Request $request)
    {
        $this->ensureAdministrator();

        $validated = $request->validate($this->validationRules());
        $validated['password'] = Hash::make($validated['password']);
        $validated['activo'] = true;
        $validated['fecha_baja'] = null;

        $usuario = Usuario::create($validated);

        $this->registrarAuditoria(
            $request,
            'USUARIO_CREADO',
            $usuario,
            "Nuevo usuario creado: {$usuario->usuario} ({$usuario->nombre})"
        );

        return redirect()->route('usuarios.index')->with('success', 'Usuario registrado correctamente.');
    }

    public function update(Request $request, int $usuario)
    {
        $this->ensureAdministrator();

        $usuario = Usuario::findOrFail($usuario);
        $validated = $request->validate($this->validationRules($usuario));

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (
            $usuario->activo
            && (int) $usuario->id_rol === 1
            && (int) $validated['id_rol'] !== 1
        ) {
            if ((int) $usuario->getKey() === (int) Auth::id()) {
                return back()
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->withErrors(['id_rol' => 'No puedes cambiar tu propio rol de administrador.']);
            }

            if (! $this->hasAnotherActiveAdministrator($usuario)) {
                return back()
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->withErrors(['id_rol' => 'No puedes cambiar el rol del único administrador activo.']);
            }
        }

        $usuario->update($validated);

        $this->registrarAuditoria(
            $request,
            'USUARIO_ACTUALIZADO',
            $usuario,
            "Datos actualizados para el usuario {$usuario->usuario}"
        );

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, int $usuario)
    {
        $this->ensureAdministrator();

        $usuario = Usuario::findOrFail($usuario);
        $nuevoEstado = ! $usuario->activo;

        if (! $nuevoEstado && (int) $usuario->getKey() === (int) Auth::id()) {
            return back()->with('error', 'No puedes dar de baja tu propia cuenta.');
        }

        if (
            ! $nuevoEstado
            && (int) $usuario->id_rol === 1
            && ! $this->hasAnotherActiveAdministrator($usuario)
        ) {
            return back()->with('error', 'No puedes dar de baja al único administrador activo.');
        }

        $usuario->update([
            'activo' => $nuevoEstado,
            'fecha_baja' => $nuevoEstado ? null : now(),
        ]);

        $accion = $nuevoEstado ? 'USUARIO_REACTIVADO' : 'USUARIO_DADO_DE_BAJA';
        $estado = $nuevoEstado ? 'reactivado' : 'dado de baja';

        $this->registrarAuditoria(
            $request,
            $accion,
            $usuario,
            "Usuario {$usuario->usuario} fue {$estado}"
        );

        return redirect()->route('usuarios.index')->with('success', "El usuario {$usuario->usuario} fue {$estado}.");
    }

    private function validationRules(?Usuario $usuario = null): array
    {
        return [
            'id_rol' => [
                'required',
                'integer',
                Rule::exists('troles', 'id_rol')->where('activo', true),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'usuario' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tusuarios', 'usuario')->ignore($usuario?->id_usuario, 'id_usuario'),
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('tusuarios', 'email')->ignore($usuario?->id_usuario, 'id_usuario'),
            ],
            'password' => [$usuario ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
            'sexo' => ['nullable', Rule::in(['M', 'F'])],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:150'],
            'sobre_mi' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function ensureAdministrator(): void
    {
        abort_unless(Auth::user()?->esAdmin(), 403);
    }

    private function hasAnotherActiveAdministrator(Usuario $usuario): bool
    {
        return Usuario::where('activo', true)
            ->where('id_rol', 1)
            ->where('id_usuario', '!=', $usuario->id_usuario)
            ->exists();
    }

    private function registrarAuditoria(
        Request $request,
        string $accion,
        Usuario $usuario,
        string $detalle
    ): void {
        DB::table('tauditoria')->insert([
            'id_usuario' => Auth::id(),
            'accion' => $accion,
            'tabla_afectada' => 'tusuarios',
            'id_registro' => $usuario->id_usuario,
            'detalle' => $detalle,
            'ip' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
