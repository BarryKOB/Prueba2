<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mueble;
use Illuminate\Support\Facades\Cookie; 
use Illuminate\Support\Facades\Session;

class TiendaController extends Controller
{
    public function index(Request $request)
    {
        // 1. LECTURA DE PREFERENCIAS (R1.c, R1.b)
        $itemsPerPage = (int)$request->cookie('preferencia_paginacion', 12);
        $monedaSimbolo = $request->cookie('preferencia_moneda', '€'); 
        $page = $request->query('page', 1);

        // 2. CONSTRUCCIÓN DEL CATÁLOGO LEYENDO MOCKS Y DATOS DE ADMIN
        // Obtenemos los Mocks ya actualizados con el stock de las cookies
        $mueblesBase = Mueble::getAllMockData(); 
        $mueblesAdmin = Session::get('muebles', []); // Muebles creados por el CRUD

        // Combinamos: Si Admin crea MESA1, sobrescribe el mock MESA1.
        $mueblesTotal = array_merge($mueblesBase, $mueblesAdmin); 
        
        $mueblesConStockActualizado = [];

        foreach ($mueblesTotal as $id => $mueble) {
            // Construimos el objeto Mueble con el stock final
            $mueblesConStockActualizado[$id] = new Mueble(
                $mueble['id'] ?? $id, $mueble['nombre'] ?? '', $mueble['categoria_id'] ?? [], $mueble['descripcion'] ?? null,
                $mueble['precio'] ?? 0, $mueble['stock'] ?? 0, 
                $mueble['materiales'] ?? null, $mueble['dimensiones'] ?? null, $mueble['color_principal'] ?? null,
                $mueble['destacado'] ?? false, []
            );
        }

        // 3. LÓGICA DE PAGINACIÓN MANUAL (R1.c, R3.b.iii)
        $mueblesArray = array_values($mueblesConStockActualizado);
        $totalItems = count($mueblesArray);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        $mueblesPaginated = array_slice($mueblesArray, $offset, $itemsPerPage, true);

        // 4. PREPARAR VARIABLES PARA LA VISTA
        $currentQuery = $request->query(); 
        return view('catalogomuebles.index', compact(
            'mueblesPaginated', 'monedaSimbolo', 'page', 'totalPages', 'totalItems', 'currentQuery'
        ));
    }

    public function show(Request $request, string $id)
    {
        // Esta función lee el detalle del mueble, el stock se lee de la cookie.
        $val = $request->cookies->get("mueble_{$id}");
        
        // Si no existe la cookie, intentamos usar el mock data base como fallback.
        if (!$val) {
             $mueble = Mueble::getAllMockData()[$id] ?? null;
             if (!$mueble) abort(404);
             $arr = $mueble; // Usar el mock como array
        } else {
             $arr = json_decode($val, true);
             if (!is_array($arr)) abort(404);
             $mueble = $arr;
        }

        $mueble = new Mueble(
            $arr['id'], $arr['nombre'] ?? '', $arr['categoria_id'] ?? [], $arr['descripcion'] ?? null,
            $arr['precio'] ?? 0, $arr['stock'] ?? 0, 
            $arr['materiales'] ?? null, $arr['dimensiones'] ?? null, $arr['color_principal'] ?? null,
            $arr['destacado'] ?? false, []
        );

        return view('catalogomuebles.show', compact('mueble'));
    }
}