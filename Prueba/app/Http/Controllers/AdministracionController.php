<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie; // Necesario para leer la cookie de paginación

class AdministracionController extends Controller
{
    // ... index() ...

    public function principal(Request $request) // RECIBIR Request para Cookies y Paginación
    {
        $usuario = Session::has('usuario') ? json_decode(Session::get('usuario')) : null;

        // 1. LECTURA DE COOKIE DE PAGINACIÓN (R1.c)
        $itemsPerPage = (int)$request->cookie('preferencia_paginacion', 12);
        $page = $request->query('page', 1);

        // 2. MOCK DATA (Se queda aquí por simplicidad)
        $muebles = [
            'MESA1' => ['id' => 'MESA1', 'nombre' => 'Mesa de Comedor Lusso', 'precio' => 250.00, 'stock' => 5],
            'SOFA2' => ['id' => 'SOFA2', 'nombre' => 'Sofá Modular Confort', 'precio' => 850.00, 'stock' => 12],
            // ... (Otros muebles, etc.)
        ];
        
        // 3. LÓGICA DE PAGINACIÓN MANUAL (R1.c, R3.b.iii)
        $totalItems = count($muebles);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        $mueblesPaginated = array_slice($muebles, $offset, $itemsPerPage, true); // Añadimos 'true' para mantener claves

        // 4. PREPARAR VARIABLES PARA LA VISTA (incluyendo la cookie de moneda)
        $monedaSimbolo = $request->cookie('preferencia_moneda', 'EUR');
        $currentQuery = $request->query(); // Obtener todos los parámetros de la URL

        return view('principal', compact(
            'usuario', 
            'mueblesPaginated', 
            'monedaSimbolo', 
            'page', 
            'totalPages', 
            'totalItems',
            'currentQuery' // Pasamos los parámetros actuales para la paginación dinámica
        ));
    }
}