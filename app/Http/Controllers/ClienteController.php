<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdministrator();
        $validated = $request->validate($this->filterRules());
        $buscar = trim($validated['buscar'] ?? '');

        $clientes = Cliente::query()
            ->when(($validated['estado'] ?? null) === 'activo', fn (Builder $query) => $query->where('activo', true))
            ->when(($validated['estado'] ?? null) === 'inactivo', fn (Builder $query) => $query->where('activo', false))
            ->when($buscar !== '', function (Builder $query) use ($buscar): void {
                $query->where(function (Builder $query) use ($buscar): void {
                    $query->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('dni_ruc', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('telefono', 'like', "%{$buscar}%");
                });
            })
            ->orderByDesc('id_cliente')
            ->paginate(15)
            ->withQueryString();

        $clienteEditando = null;
        return view('clientes.index', compact('clientes', 'clienteEditando'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdministrator();
        $validated = $request->validate($this->validationRules());

        $validated['activo'] = true;
        $cliente = Cliente::create($validated + ['activo' => true, 'fecha_baja' => null]);
        $this->registrarAuditoria($request, 'CLIENTE_CREADO', $cliente, "Nuevo cliente creado: {$cliente->nombres}");

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado exitosamente.');
    }

    public function edit(Request $request, int $cliente): View
    {
        $this->ensureAdministrator();
        return $this->renderIndex($request, Cliente::findOrFail($cliente));
    }

    public function update(Request $request, int $cliente): RedirectResponse
    {
        $this->ensureAdministrator();
        $cliente = Cliente::findOrFail($cliente);
        $cliente->update($request->validate($this->validationRules($cliente)));
        $this->registrarAuditoria($request, 'CLIENTE_ACTUALIZADO', $cliente, "Datos actualizados para el cliente {$cliente->nombres}");
        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->ensureAdministrator();
        $cliente = Cliente::findOrFail($id);
        $activo = ! $cliente->activo;
        $cliente->update(['activo' => $activo, 'fecha_baja' => $activo ? null : now()]);
        $this->registrarAuditoria($request, $activo ? 'CLIENTE_REACTIVADO' : 'CLIENTE_DADO_DE_BAJA', $cliente, "Cliente {$cliente->nombres} fue ".($activo ? 'reactivado' : 'dado de baja'));

        return redirect()->route('clientes.index')->with('success', 'Cliente '.($activo ? 'reactivado' : 'dado de baja').'.');
    }

    private function renderIndex(Request $request, ?Cliente $clienteEditando = null): View
    {
        $validated = $request->validate($this->filterRules());
        $buscar = trim($validated['buscar'] ?? '');
        $clientes = Cliente::query()->when(($validated['estado'] ?? null) === 'activo', fn (Builder $q) => $q->where('activo', true))->when(($validated['estado'] ?? null) === 'inactivo', fn (Builder $q) => $q->where('activo', false))->when($buscar !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q->where('nombres', 'like', "%{$buscar}%")->orWhere('dni_ruc', 'like', "%{$buscar}%")->orWhere('email', 'like', "%{$buscar}%")->orWhere('telefono', 'like', "%{$buscar}%")))->latest('id_cliente')->paginate(15)->withQueryString();
        return view('clientes.index', compact('clientes', 'clienteEditando'));
    }

    private function filterRules(): array { return ['buscar' => ['nullable', 'string', 'max:100'], 'estado' => ['nullable', Rule::in(['activo', 'inactivo'])]]; }
    private function validationRules(?Cliente $cliente = null): array { return ['tipo_documento' => ['required', Rule::in(['DNI', 'RUC', 'CE'])], 'dni_ruc' => ['required', 'string', 'max:20', Rule::unique('tclientes', 'dni_ruc')->ignore($cliente?->id_cliente, 'id_cliente')], 'nombres' => ['required', 'string', 'max:150'], 'email' => ['nullable', 'email', 'max:100'], 'telefono' => ['nullable', 'string', 'max:20'], 'direccion' => ['nullable', 'string', 'max:200'], 'fecha_nacimiento' => ['nullable', 'date']]; }
    private function ensureAdministrator(): void { abort_unless(Auth::user()?->esAdmin(), 403); }
    private function registrarAuditoria(Request $request, string $accion, Cliente $cliente, string $detalle): void { DB::table('tauditoria')->insert(['id_usuario' => Auth::id(), 'accion' => $accion, 'tabla_afectada' => 'tclientes', 'id_registro' => $cliente->id_cliente, 'detalle' => $detalle, 'ip' => $request->ip(), 'created_at' => now(), 'updated_at' => now()]); }
}
