<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;

class AdministracionController extends Controller
{
    public function index()
    {
        // ... (Lógica de protección para dashboard) ...
        if (!Session::has('autorizacion_usuario') || !Session::get('autorizacion_usuario')) {
            return redirect()->route('login')->withErrors(['error' => 'Debes iniciar sesión.']);
        }

        $usuario = json_decode(Session::get('usuario'));

        return view('dashboard', compact('usuario'));
    }

    public function principal()
    {
        // Catálogo principal: solo lee el usuario si está logueado, pero permite el acceso general.
        $usuario = Session::has('usuario') ? json_decode(Session::get('usuario')) : null;

        return view('principal', compact('usuario'));
    }
}