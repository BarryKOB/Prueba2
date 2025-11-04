<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mueble;
use Illuminate\Support\Facades\Cookie; // Necesario para leer preferencias

class TiendaController extends Controller
{
    public function index(Request $request)
    {
        // 1. LECTURA DE PREFERENCIAS (R1.c, R1.b)
        $itemsPerPage = (int)$request->cookie('preferencia_paginacion', 12);
        $monedaSimbolo = $request->cookie('preferencia_moneda', 'EUR');
        $page = $request->query('page', 1);

        // 2. LECTURA DE DATOS (Leyendo de cookies/Mock)
        $rawMuebles = [];
        
        // Simulación: leemos todos los muebles que fueron creados y guardados en cookies por el admin
        foreach ($request->cookies->all() as $key => $value) {
            if (strpos($key, 'mueble_') === 0) {
                $arr = json_decode($value, true);
                if (is_array($arr) && isset($arr['id'])) {
                    // MUEBLE INSTANCE CREATION
                    $rawMuebles[$arr['id']] = new Mueble(
                        $arr['id'], $arr['nombre'] ?? '', $arr['categoria_id'] ?? [], $arr['descripcion'] ?? null,
                        $arr['precio'] ?? 0, $arr['stock'] ?? 0, $arr['materiales'] ?? null,
                        $arr['dimensiones'] ?? null, $arr['color_principal'] ?? null, $arr['destacado'] ?? false, []
                    );
                }
            }
        }
        
        // CORRECCIÓN: Fallback a Mock Data del Modelo Mueble si no hay datos de Admin
        if (empty($rawMuebles)) {
             $rawMuebles = Mueble::getAllMockData(); // <--- LLAMADA CORREGIDA
        }

        // 3. LÓGICA DE PAGINACIÓN MANUAL (R1.c, R3.b.iii)
        $mueblesArray = array_values($rawMuebles);
        $totalItems = count($mueblesArray);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        $mueblesPaginated = array_slice($mueblesArray, $offset, $itemsPerPage, true);

        // 4. PREPARAR VARIABLES PARA LA VISTA
        $currentQuery = $request->query(); // Obtener todos los parámetros de la URL para la paginación

        return view('principal', compact(
            'mueblesPaginated', 'monedaSimbolo', 'page', 'totalPages', 'totalItems', 'currentQuery'
        ));
    }

    public function show(Request $request, string $id)
    {
        $val = $request->cookies->get("mueble_{$id}");
        if (!$val) {
            abort(404);
        }

        $arr = json_decode($val, true);
        if (!is_array($arr)) {
            abort(404);
        }

        $mueble = new Mueble(
            $arr['id'],
            $arr['nombre'] ?? '',
            $arr['categoria_id'] ?? [],
            $arr['descripcion'] ?? null,
            $arr['precio'] ?? 0,
            $arr['stock'] ?? 0,
            $arr['materiales'] ?? null,
            $arr['dimensiones'] ?? null,
            $arr['color_principal'] ?? null,
            $arr['destacado'] ?? false,
            []
        );

        return view('catalogomuebles.show', compact('mueble'));
    }
}