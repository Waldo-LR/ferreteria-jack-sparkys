<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function index(): View
    {
        $this->ensureAdministrator();

        $configuracion = Configuracion::find(1) ?? new Configuracion([
            'razon_social' => '',
            'ruc' => '',
            'direccion' => '',
            'telefono' => '',
            'email' => '',
            'slogan' => '',
            'igv_porcentaje' => 18,
            'mora_porcentaje' => 15,
            'dias_recuperacion' => 0,
            'dias_alerta_vencimiento' => 0,
            'cierre_automatico' => false,
        ]);

        return view('configuracion.index', compact('configuracion'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensureAdministrator();

        $validated = $request->validate([
            'razon_social' => ['required', 'string', 'max:150'],
            'ruc' => ['required', 'digits:11'],
            'direccion' => ['required', 'string', 'max:200'],
            'telefono' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100'],
            'slogan' => ['nullable', 'string', 'max:200'],
            'igv_porcentaje' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'mora_porcentaje' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'dias_recuperacion' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'dias_alerta_vencimiento' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'cierre_automatico' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $configuracion = Configuracion::find(1);

            if ($configuracion === null) {
                $configuracion = new Configuracion;
                $configuracion->id_configuracion = 1;
            }

            $configuracion->fill($validated);
            $configuracion->save();

            Auditoria::create([
                'id_usuario' => Auth::id(),
                'accion' => 'CONFIGURACION_ACTUALIZADA',
                'tabla_afectada' => 'tconfiguracion',
                'id_registro' => $configuracion->id_configuracion,
                'detalle' => 'Actualización de configuración empresarial. Campos: '.implode(', ', array_keys($validated)),
                'ip' => $request->ip(),
            ]);
        });

        return redirect()
            ->route('configuracion.index')
            ->with('success', 'La configuración de la empresa se actualizó correctamente.');
    }

    private function ensureAdministrator(): void
    {
        abort_unless(Auth::user()?->esAdmin(), 403);
    }
}
