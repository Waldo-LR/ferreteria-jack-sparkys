<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::with('rol')->latest('id_usuario');

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('nombre', 'like', "%$b%")
                  ->orWhere('usuario', 'like', "%$b%")
                  ->orWhere('email', 'like', "%$b%");
            });
        }

        $usuarios = $query->paginate(15);
        $roles = Rol::where('activo', 1)->get();
        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_rol' => 'required|integer',
            'nombre' => 'required|string|max:100',
            'usuario' => 'required|string|max:50|unique:tusuarios,usuario',
            'email' => 'required|email|max:100|unique:tusuarios,email',
            'password' => 'required|string|min:6',
            'sexo' => 'nullable|in:M,F',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:150',
        ]);

        $validated['password'] = Hash::make($request->password);
        $validated['activo'] = 1;

        $usr = Usuario::create($validated);

        DB::table('tauditoria')->insert([
            'id_usuario' => Auth::id() ?? 1,
            'accion' => 'USUARIO_CREADO',
            'tabla_afectada' => 'tusuarios',
            'id_registro' => $usr->id_usuario,
            'detalle' => "Nuevo usuario creado: {$usr->usuario} ({$usr->nombre})",
            'ip' => $request->ip(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario registrado en el sistema correctamente.');
    }

    public function destroy($id)
    {
        $usr = Usuario::findOrFail($id);
        $nuevoEstado = $usr->activo == 1 ? 0 : 1;
        $usr->update(['activo' => $nuevoEstado]);

        $accionTxt = $nuevoEstado == 1 ? 'activado' : 'dado de baja';

        DB::table('tauditoria')->insert([
            'id_usuario' => Auth::id() ?? 1,
            'accion' => 'USUARIO_ESTADO_CAMBIADO',
            'tabla_afectada' => 'tusuarios',
            'id_registro' => $usr->id_usuario,
            'detalle' => "Usuario {$usr->usuario} fue {$accionTxt}",
            'ip' => request()->ip(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->route('usuarios.index')->with('success', "Usuario {$usr->usuario} {$accionTxt} con éxito.");
    }
}