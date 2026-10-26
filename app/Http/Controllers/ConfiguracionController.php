<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $config = DB::table('tconfiguracion')->first();
        return view('configuracion.index', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'razon_social' => 'required|string|max:150',
            'ruc' => 'required|string|max:11',
            'direccion' => 'required|string|max:200',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|max:100',
            'slogan' => 'nullable|string|max:150',
            'igv_porcentaje' => 'required|numeric|min:0|max:100',
            'mora_porcentaje' => 'required|numeric|min:0|max:100',
        ]);

        $data = [
            'razon_social' => $request->razon_social,
            'ruc' => $request->ruc,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'slogan' => $request->slogan,
            'igv_porcentaje' => $request->igv_porcentaje,
            'mora_porcentaje' => $request->mora_porcentaje,
            'updated_at' => Carbon::now(),
        ];

        DB::table('tconfiguracion')->where('id_configuracion', 1)->update($data);

        DB::table('tauditoria')->insert([
            'id_usuario' => Auth::id() ?? 1,
            'accion' => 'CONFIGURACION_ACTUALIZADA',
            'tabla_afectada' => 'tconfiguracion',
            'id_registro' => 1,
            'detalle' => 'Actualización de datos generales de la ferretería',
            'ip' => $request->ip(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return back()->with('success', '¡Configuración de la empresa actualizada correctamente!');
    }
}