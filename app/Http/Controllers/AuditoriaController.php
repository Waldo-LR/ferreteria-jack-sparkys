<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Auditoria;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Auditoria::with('usuario')->latest('id_auditoria');

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('accion', 'like', "%$b%")
                  ->orWhere('tabla_afectada', 'like', "%$b%")
                  ->orWhere('detalle', 'like', "%$b%")
                  ->orWhereHas('usuario', function ($qu) use ($b) {
                      $qu->where('nombre', 'like', "%$b%")
                         ->orWhere('usuario', 'like', "%$b%");
                  });
            });
        }

        $auditorias = $query->paginate(20);
        return view('auditoria.index', compact('auditorias'));
    }
}