<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderIndex($request);
    }

    public function edit(Request $request, $id)
    {
        $proveedor = Proveedor::where('activo', 1)->findOrFail($id);

        return $this->renderIndex($request, $proveedor);
    }

    private function renderIndex(Request $request, ?Proveedor $proveedorEditando = null)
    {
        $query = Proveedor::where('activo', 1)->latest('id_proveedor');

        if ($request->filled('buscar')) {
            $buscar = $request->string('buscar')->trim()->toString();
            $query->where(function ($q) use ($buscar) {
                $q->where('razon_social', 'like', "%$buscar%")
                    ->orWhere('ruc', 'like', "%$buscar%")
                    ->orWhere('email', 'like', "%$buscar%")
                    ->orWhere('telefono', 'like', "%$buscar%");
            });
        }

        $proveedores = $query->paginate(15)->withQueryString();

        return view('proveedores.index', compact('proveedores', 'proveedorEditando'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        $validated['activo'] = 1;
        $validated['fecha_baja'] = null;
        $proveedor = Proveedor::create($validated);

        $this->registrarAuditoria($request, 'PROVEEDOR_CREADO', $proveedor, "Proveedor registrado: {$proveedor->razon_social} (RUC: {$proveedor->ruc})");

        return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::where('activo', 1)->findOrFail($id);
        $proveedor->update($request->validate($this->validationRules($proveedor)));

        $this->registrarAuditoria($request, 'PROVEEDOR_ACTUALIZADO', $proveedor, "Proveedor actualizado: {$proveedor->razon_social} (RUC: {$proveedor->ruc})");

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::where('activo', 1)->findOrFail($id);
        $proveedor->update([
            'activo' => 0,
            'fecha_baja' => now(),
        ]);

        $this->registrarAuditoria(
            request(),
            'PROVEEDOR_ELIMINADO',
            $proveedor,
            "Proveedor dado de baja: {$proveedor->razon_social} (RUC: {$proveedor->ruc})"
        );

        return redirect()->route('proveedores.index')->with('success', 'Proveedor dado de baja correctamente.');
    }

    private function validationRules(?Proveedor $proveedor = null): array
    {
        return [
            'razon_social' => ['required', 'string', 'max:150'],
            'ruc' => [
                'required',
                'digits:11',
                Rule::unique('tproveedores', 'ruc')->ignore($proveedor?->id_proveedor, 'id_proveedor'),
            ],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:200'],
        ];
    }

    private function registrarAuditoria(Request $request, string $accion, Proveedor $proveedor, string $detalle): void
    {
        DB::table('tauditoria')->insert([
            'id_usuario' => Auth::id() ?? 1,
            'accion' => $accion,
            'tabla_afectada' => 'tproveedores',
            'id_registro' => $proveedor->id_proveedor,
            'detalle' => $detalle,
            'ip' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
