<?php

namespace App\Http\Controllers;

use App\Enums\RolUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function mostrar()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:4'],
        ]);

        // Verificar usuario
        $usuario = User::verificarUsuario($datos['email'], $datos['password']);

        if (!$usuario) {
            return back()->withErrors(['errorCredenciales' => 'Credenciales incorrectas.']);
        }

        $datosSesion = [
            'email' => $usuario->email,
            'nombre'  => $usuario->nombre,
            'rol' => $usuario->rol, // ROL AÑADIDO
            'fecha_ingreso' => now()->toDateTimeString(),
        ];

        // Guardar usuario en sesión (Requerimiento 2.c)
        Session::put('usuario', json_encode($datosSesion));
        Session::put('autorizacion_usuario', true);
        Session::regenerate();

        // Si el usuario marcó "Recordarme"
        if ($request->has('recuerdame')) {
            config(['session.lifetime' => 43200]); 
            config(['expire_on_close' => true]);
        }

        // Redirección basada en Rol
        if ($usuario->rol === \App\Enums\RolUser::ADMIN) { 
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('principal');
        }
    }

   // ... dentro de LoginController.php

   public function cerrarSesion()
   {
       // Corrección: Solo olvidamos las claves de autenticación (Requerimiento 2.d)
       Session::forget('usuario');
       Session::forget('autorizacion_usuario');
       
       // NO HACEMOS Session::flush()

       Session::regenerate(); 

       return redirect()->route('login')->with('mensaje', 'Sesión cerrada correctamente.');
   }
}