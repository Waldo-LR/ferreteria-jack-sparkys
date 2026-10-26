<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $request->usuario;

        $user = Usuario::with('rol')
            ->where(function ($q) use ($loginInput) {
                $q->where('usuario', $loginInput)
                  ->orWhere('email', $loginInput);
            })
            ->where('activo', 1)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            $user->update(['ultimo_login' => Carbon::now()]);

            // Auditoría básica de inicio de sesión
            DB::table('tauditoria')->insert([
                'id_usuario' => $user->id_usuario,
                'accion' => 'LOGIN_EXITOSO',
                'tabla_afectada' => 'tusuarios',
                'id_registro' => $user->id_usuario,
                'detalle' => 'Inicio de sesión en el sistema con rol ' . ($user->rol->nombre ?? 'Usuario'),
                'ip' => $request->ip(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return redirect()->intended(route('dashboard'))
                ->with('success', '¡Bienvenido(a), ' . $user->nombre . '! Has ingresado como ' . ($user->rol->nombre ?? 'Personal'));
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales ingresadas no coinciden con nuestros registros o la cuenta está inactiva.',
        ])->onlyInput('usuario');
    }

    public function quickLogin($role)
    {
        $roleMap = [
            'admin' => 1,
            'vendedor' => 2,
            'almacen' => 3,
            'cobranzas' => 4,
            'cajero' => 5,
        ];

        $roleId = $roleMap[$role] ?? 1;
        $user = Usuario::with('rol')->where('id_rol', $roleId)->where('activo', 1)->first();

        if (!$user) {
            $user = Usuario::with('rol')->first();
        }

        if ($user) {
            Auth::login($user);
            $user->update(['ultimo_login' => Carbon::now()]);

            return redirect()->route('dashboard')
                ->with('success', 'Modo Demostración: Has iniciado sesión como ' . ($user->rol->nombre ?? 'Personal') . ' (' . $user->nombre . ')');
        }

        return redirect()->route('login')->withErrors(['usuario' => 'Usuario no encontrado']);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            DB::table('tauditoria')->insert([
                'id_usuario' => Auth::id(),
                'accion' => 'LOGOUT',
                'tabla_afectada' => 'tusuarios',
                'id_registro' => Auth::id(),
                'detalle' => 'Cierre de sesión del sistema',
                'ip' => $request->ip(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Has cerrado sesión correctamente.');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:150',
            'sobre_mi' => 'nullable|string',
            'password' => 'nullable|string|min:6|confirmed'
        ]);

        $data = [
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'sobre_mi' => $request->sobre_mi,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        Usuario::where('id_usuario', $user->id_usuario)->update($data);

        // Registro de auditoría
        DB::table('tauditoria')->insert([
            'id_usuario' => $user->id_usuario,
            'accion' => 'PERFIL_ACTUALIZADO',
            'tabla_afectada' => 'tusuarios',
            'id_registro' => $user->id_usuario,
            'detalle' => 'Actualización de datos personales / contraseña',
            'ip' => $request->ip(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return back()->with('success', 'Tu perfil ha sido actualizado con éxito.');
    }
}
