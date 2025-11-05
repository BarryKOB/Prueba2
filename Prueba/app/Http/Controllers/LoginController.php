<?php

namespace App\Http\Controllers;

use App\Enums\RolUser;
use App\Enums\RolUsuario;
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

        // CORRECCIÓN CRÍTICA: Extraer el valor de cadena del Enum ANTES de guardar en sesión (R2.c)
        // Esto soluciona el problema de desincronización del rol que causaba el 403 en el MuebleController.
        $datosSesion = [
            'email' => $usuario->email,
            'nombre'  => $usuario->nombre,
            'rol' => $usuario->rol->value, // AHORA GUARDA LA CADENA 'admin'
            'fecha_ingreso' => now()->toDateTimeString(),
        ];

        // Guardar usuario en sesión
        Session::put('usuario', json_encode($datosSesion));
        Session::put('autorizacion_usuario', true);
        Session::regenerate();

        // Si el usuario marcó "Recordarme"
        if ($request->has('recuerdame')) {
            config(['session.lifetime' => 43200]); 
            config(['expire_on_close' => true]);
        }

        // Redirección basada en Rol
        if ($usuario->rol->value === RolUser::ADMIN->value) { 
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('principal');
        }
    }

    public function cerrarSesion()
    {
        // CORRECCIÓN CRÍTICA PARA R4.c: Usamos forget para preservar el carrito.
        Session::forget('usuario');
        Session::forget('autorizacion_usuario');
        
        Session::regenerate(); 

        return redirect()->route('login')->with('mensaje', 'Sesión cerrada correctamente.');
    }
}