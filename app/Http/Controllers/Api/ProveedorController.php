<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function index()
    {
        $data = Proveedor::where('activo', 1)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());
        $data['activo'] = 1;
        $data['fecha_baja'] = null;
        $item = Proveedor::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Registro creado correctamente.',
            'data' => $item,
        ], 201);
    }

    public function show($id)
    {
        $item = Proveedor::where('activo', 1)->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $item], 200);
    }

    public function update(Request $request, $id)
    {
        $item = Proveedor::where('activo', 1)->findOrFail($id);
        $item->update($request->validate($this->validationRules($item, $request->isMethod('patch'))));

        return response()->json([
            'status' => 'success',
            'message' => 'Registro actualizado correctamente.',
            'data' => $item,
        ], 200);
    }

    public function destroy($id)
    {
        $item = Proveedor::where('activo', 1)->findOrFail($id);
        $item->update([
            'activo' => 0,
            'fecha_baja' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registro dado de baja correctamente.',
        ], 200);
    }

    private function validationRules(?Proveedor $proveedor = null, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'razon_social' => [$required, 'string', 'max:150'],
            'ruc' => [
                $required,
                'digits:11',
                Rule::unique('tproveedores', 'ruc')->ignore($proveedor?->id_proveedor, 'id_proveedor'),
            ],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:200'],
        ];
    }
}
