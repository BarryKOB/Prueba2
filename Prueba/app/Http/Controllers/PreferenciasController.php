<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class PreferenciasController extends Controller
{
    public function edit(Request $request)
    {
        if (!Session::has('autorizacion_usuario')) {
            return redirect()->route('login');
        }

        $tema = $request->cookie('preferencia_tema', 'claro'); 
        $moneda = $request->cookie('preferencia_moneda', 'EUR');
        $paginacion = $request->cookie('preferencia_paginacion', 12);

        // CORRECCIÓN CRÍTICA: Usar compact() para asegurar el paso de variables
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

        // Validación de datos
        $data = $request->validate([
            'tema' => ['required', 'in:claro,obscuro'],
            'moneda' => ['required', 'in:EUR,USD,GBP'],
            'paginacion' => ['required', 'integer', 'in:6,12,24'],
        ]);

        $minutes = 60 * 24 * 30; // 30 días de persistencia (Requerimiento 1.d)
        
        // Guardado de Cookies (Requerimiento 1)
        Cookie::queue('preferencia_tema', $data['tema'], $minutes);
        Cookie::queue('preferencia_moneda', $data['moneda'], $minutes);
        Cookie::queue('preferencia_paginacion', $data['paginacion'], $minutes);

        return redirect()->back()->with('mensaje', 'Preferencias guardadas correctamente');
    }
}
        
    