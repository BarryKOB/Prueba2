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
        $mueblesBase = Mueble::getAllMockData(); // Mocks base (Stock: 5)
        $mueblesAdmin = Session::get('muebles', []);
        $mueblesTotal = array_merge($mueblesBase, $mueblesAdmin); 
        $mueblesConStockActualizado = [];


        foreach ($mueblesTotal as $id => $mueble) {
            
            // CONVERTIMOS EL OBJETO MUEBLE (si viene de la sesión) A ARRAY para trabajar con él
            if ($mueble instanceof Mueble) {
                // Si ya es un objeto Mueble, lo convertimos a array para el manejo de stock
                $mueble = $mueble->jsonSerialize();
            }
            
            $cookieName = "mueble_{$id}";
            $cookieData = $request->cookie($cookieName); 
            
            // Si hay una cookie de stock actualizada, la lee (R4.d)
            if ($cookieData) {
                $arr = json_decode($cookieData, true);
                
                // CRÍTICO: Sobreescribir el stock del Mock/Session con el valor de la Cookie
                if (isset($arr['stock'])) {
                    // Si el elemento es una simple array, esto funciona:
                    $mueble['stock'] = $arr['stock']; 
                }
            }
            
            // Construimos el objeto Mueble FINAL para la vista
            $mueblesConStockActualizado[$id] = new Mueble(
                $mueble['id'] ?? $id, $mueble['nombre'] ?? '', $mueble['categoria_id'] ?? [], $mueble['descripcion'] ?? null,
                $mueble['precio'] ?? 0,
                $mueble['stock'] ?? 0,
                $mueble['materiales'] ?? null, $mueble['dimensiones'] ?? null, $mueble['color_principal'] ?? null,
                $mueble['destacado'] ?? false,
                $mueble['imagenes'] ?? []
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
        
        if (!$val) {
             $mueble = Mueble::getAllMockData()[$id] ?? null;
             if (!$mueble) abort(404);
             $arr = $mueble; 
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