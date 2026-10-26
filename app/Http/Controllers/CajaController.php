<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\GastoCaja;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index()
    {
        $cajaActual = Caja::where("estado", "abierta")->first();
        $gastos = $cajaActual ? GastoCaja::where("id_caja", $cajaActual->id_caja)->get() : collect();
        return view("caja.index", compact("cajaActual", "gastos"));
    }
}
