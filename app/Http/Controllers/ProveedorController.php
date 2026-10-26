<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::where('activo', 1)->latest('id_proveedor');

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('razon_social', 'like', "%$b%")
                  ->orWhere('ruc', 'like', "%$b%")
                  ->orWhere('email', 'like', "%$b%")
                  ->orWhere('telefono', 'like', "%$b%");
            });
        }

        $proveedores = $query->paginate(15);
        return view('proveedores.index', compact('proveedores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'razon_social' => 'required|string|max:150',
            'ruc' => 'required|string|max:11',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:200',
        ]);

        $validated['activo'] = 1;
        $prov = Proveedor::create($validated);

        DB::table('tauditoria')->insert([
            'id_usuario' => Auth::id() ?? 1,
            'accion' => 'PROVEEDOR_CREADO',
            'tabla_afectada' => 'tproveedores',
            'id_registro' => $prov->id_proveedor,
            'detalle' => "Nuevo proveedor registrado: {$prov->razon_social} (RUC: {$prov->ruc})",
            'ip' => $request->ip(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado exitosamente.');
    }

    public function destroy($id)
    {
        $prov = Proveedor::findOrFail($id);
        $prov->update(['activo' => 0]);

        DB::table('tauditoria')->insert([
            'id_usuario' => Auth::id() ?? 1,
            'accion' => 'PROVEEDOR_ELIMINADO',
            'tabla_afectada' => 'tproveedores',
            'id_registro' => $prov->id_proveedor,
            'detalle' => "Proveedor dado de baja: {$prov->razon_social}",
            'ip' => request()->ip(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor dado de baja correctamente.');
    }
}