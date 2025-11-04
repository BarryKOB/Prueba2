<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class PreferenciasController extends Controller
{
    public function edit()
    {
        // Comprobar autenticación manualmente
        if (!Session::has('autorizacion_usuario')) {
            return redirect()->route('login');
        }

        $moneda = Cookie::get('moneda', 'EUR');
        $paginacion = Cookie::get('paginacion', '12');

        return view('preferencias', [
            'moneda' => $moneda,
            'paginacion' => $paginacion
        ]);
    }

    public function update(Request $request)
    {
        if (!Session::has('autorizacion_usuario')) {
            return redirect()->route('login');
        }

        $minutes = 60 * 24 * 30;
        Cookie::queue('moneda', $request->moneda, $minutes);
        Cookie::queue('paginacion', $request->paginacion, $minutes);

        return redirect()->back()->with('mensaje', 'Preferencias guardadas correctamente');
    }
}


