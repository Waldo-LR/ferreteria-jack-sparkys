<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'usuario' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
            'nombre_token' => ['nullable', 'string', 'max:100'],
        ]);

        $usuario = Usuario::query()
            ->where(function (Builder $query) use ($datos): void {
                $query->where('usuario', $datos['usuario'])
                    ->orWhere('email', $datos['usuario']);
            })
            ->where('activo', true)
            ->first();

        if (! $usuario || ! Hash::check($datos['password'], $usuario->password)) {
            return response()->json([
                'message' => 'Las credenciales no son válidas o la cuenta está inactiva.',
            ], 401);
        }

        $usuario->update(['ultimo_login' => now()]);
        $expirationMinutes = (int) config('sanctum.expiration', 1440);
        $token = $usuario->createToken(
            $datos['nombre_token'] ?? 'Postman',
            ['*'],
            now()->addMinutes($expirationMinutes)
        );

        return response()->json([
            'message' => 'Autenticación correcta.',
            'data' => [
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->accessToken->expires_at,
                'usuario' => [
                    'id_usuario' => $usuario->id_usuario,
                    'nombre' => $usuario->nombre,
                    'usuario' => $usuario->usuario,
                    'id_rol' => $usuario->id_rol,
                ],
            ],
        ], 201);
    }

    public function user(Request $request): JsonResponse
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 401);

        return response()->json([
            'data' => [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'usuario' => $usuario->usuario,
                'email' => $usuario->email,
                'id_rol' => $usuario->id_rol,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Token revocado correctamente.']);
    }
}
