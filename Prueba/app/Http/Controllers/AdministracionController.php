<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class AdministracionController extends Controller
{
    public function index()
    {
        // Esta protección sigue activa para /dashboard (R5)
        if (!Session::has('autorizacion_usuario') || !Session::get('autorizacion_usuario')) {
            return redirect()->route('login')->withErrors(['error' => 'Debes iniciar sesión.']);
        }

        $usuario = json_decode(Session::get('usuario'));

        return view('dashboard', compact('usuario'));
    }

    /**
     * Catálogo Principal (Público)
     * Redirige al controlador de tienda/catálogo para mostrar los productos.
     */
    public function principal(Request $request) 
    {
        // CORRECCIÓN: Redirigir al controlador real del catálogo público (TiendaController)
        return redirect()->route('catalogomuebles.index');
    }
}