<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mueble;

class TiendaController extends Controller
{
    public function index(Request $request)
    {
        $muebles = [];

        foreach ($request->cookies->all() as $key => $value) {
            if (strpos($key, 'mueble_') === 0) {
                $arr = json_decode($value, true);
                if (is_array($arr) && isset($arr['id'])) {
                    $muebles[$arr['id']] = new Mueble(
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
                }
            }
        }

        return view('catalogomuebles.index', compact('muebles'));
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
