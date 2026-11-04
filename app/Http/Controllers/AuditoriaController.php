<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdministrator();

        $validated = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        $query = Auditoria::with('usuario')
            ->latest('id_auditoria');

        if (! empty($validated['buscar'])) {
            $buscar = trim($validated['buscar']);

            $query->where(function ($query) use ($buscar): void {
                $query->where('accion', 'like', "%{$buscar}%")
                    ->orWhere('tabla_afectada', 'like', "%{$buscar}%")
                    ->orWhere('detalle', 'like', "%{$buscar}%")
                    ->orWhere('ip', 'like', "%{$buscar}%")
                    ->orWhereHas('usuario', function ($query) use ($buscar): void {
                        $query->where('nombre', 'like', "%{$buscar}%")
                            ->orWhere('usuario', 'like', "%{$buscar}%");
                    });
            });
        }

        if (! empty($validated['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $validated['fecha_desde']);
        }

        if (! empty($validated['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $validated['fecha_hasta']);
        }

        $auditorias = $query->paginate(20)->withQueryString();

        return view('auditoria.index', compact('auditorias'));
    }

    private function ensureAdministrator(): void
    {
        abort_unless(Auth::user()?->esAdmin(), 403);
    }
}
