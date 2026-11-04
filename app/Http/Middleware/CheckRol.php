<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRol
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $roleChecks = [
            'admin' => static fn (Usuario $user): bool => $user->esAdmin(),
            'vendedor' => static fn (Usuario $user): bool => $user->esVendedor(),
            'almacenero' => static fn (Usuario $user): bool => $user->esAlmacenero(),
            'cobrador' => static fn (Usuario $user): bool => $user->esCobrador(),
            'cajero' => static fn (Usuario $user): bool => $user->esCajero(),
        ];

        abort_unless(
            $user instanceof Usuario
                && $roles !== []
                && array_diff($roles, array_keys($roleChecks)) === []
                && collect($roles)->contains(fn (string $role): bool => $roleChecks[$role]($user)),
            403
        );

        return $next($request);
    }
}
