<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CarritoController extends Controller
{
    /**
     * Genera la clave del carrito única asociada al ID de la sesión actual (Requerimiento 4.c).
     * Esto aísla el carrito por sesión de navegador.
     */
    private function getCarritoKey(): string
    {
        return 'carrito_' . Session::getId(); 
    }
    
    /**
     * Función auxiliar para obtener datos de un mueble (Mock Data).
     */
    private function getMuebleById(string $id): ?array
    {
        // NOTA: Estos datos MOCK deben coincidir con los que están en principal.blade.php
        $mueblesMock = [
            'MESA1' => ['id' => 'MESA1', 'nombre' => 'Mesa de Comedor Lusso', 'precio' => 250.00, 'stock' => 5],
            'SOFA2' => ['id' => 'SOFA2', 'nombre' => 'Sofá Modular Confort', 'precio' => 850.00, 'stock' => 12],
            'SILLA3' => ['id' => 'SILLA3', 'nombre' => 'Silla Eames Clásica', 'precio' => 75.00, 'stock' => 0],
            'MUEBLE1' => ['id' => 'MUEBLE1', 'nombre' => 'Silla de Oficina', 'precio' => 85.50, 'stock' => 10, 'categoria_id' => 'CAT1'],
        ];

        return $mueblesMock[$id] ?? null;
    }

    /**
     * Muestra el contenido del carrito (Requerimiento 4.b).
     */
    public function show()
    {
        $carrito = Session::get($this->getCarritoKey(), []); // CLAVE DINÁMICA
        
        // CÁLCULOS (Requerimiento 4.b)
        $subtotal = array_sum(array_map(fn($item) => $item['precio'] * $item['cantidad'], $carrito));
        $impuestos = $subtotal * 0.16;
        $total = $subtotal + $impuestos;

        return view('carrito.show', compact('carrito', 'subtotal', 'impuestos', 'total'));
    }

    /**
     * Añade un mueble al carrito (Requerimiento 4.a).
     */
    public function add(Request $request, string $muebleId)
    {
        $mueble = $this->getMuebleById($muebleId);
        if (!$mueble) { return back()->with('error', 'El mueble no existe.'); }

        $cantidadAAnadir = $request->input('cantidad', 1);
        $carrito = Session::get($this->getCarritoKey(), []); // CLAVE DINÁMICA
        $stockDisponible = $mueble['stock'];
        $cantidadActual = $carrito[$muebleId]['cantidad'] ?? 0;
        $nuevaCantidad = $cantidadActual + $cantidadAAnadir;

        // Validación de Stock (Requerimiento 4.d)
        if ($nuevaCantidad > $stockDisponible) {
            return back()->with('error', 'No hay suficiente stock. Stock disponible: ' . $stockDisponible);
        }

        if (isset($carrito[$muebleId])) {
            $carrito[$muebleId]['cantidad'] = $nuevaCantidad;
        } else {
            $carrito[$muebleId] = [
                'mueble_id' => $muebleId,
                'nombre' => $mueble['nombre'],
                'precio' => $mueble['precio'],
                'cantidad' => $cantidadAAnadir,
                'stock_disponible' => $stockDisponible,
            ];
        }

        Session::put($this->getCarritoKey(), $carrito); // CLAVE DINÁMICA
        return redirect()->route('carrito.show')->with('success', $mueble['nombre'] . ' añadido al carrito.');
    }

    /**
     * Actualiza la cantidad de un ítem (Requerimiento 4.a).
     */
    public function update(Request $request, string $muebleId)
    {
        $request->validate(['cantidad' => 'required|integer|min:1']);
        $cantidad = $request->input('cantidad');
        $carrito = Session::get($this->getCarritoKey(), []); // CLAVE DINÁMICA

        if (isset($carrito[$muebleId])) {
            $stockDisponible = $carrito[$muebleId]['stock_disponible'];
            if ($cantidad > $stockDisponible) {
                return back()->with('error', 'No hay suficiente stock. Máximo: ' . $stockDisponible);
            }

            $carrito[$muebleId]['cantidad'] = $cantidad;
            Session::put($this->getCarritoKey(), $carrito); // CLAVE DINÁMICA
            return back()->with('success', 'Cantidad actualizada.');
        }

        return back()->with('error', 'Mueble no encontrado en el carrito.');
    }

    /**
     * Elimina un ítem del carrito (Requerimiento 4.a).
     */
    public function remove(string $muebleId)
    {
        $carrito = Session::get($this->getCarritoKey(), []); // CLAVE DINÁMICA

        if (isset($carrito[$muebleId])) {
            unset($carrito[$muebleId]);
            Session::put($this->getCarritoKey(), $carrito); // CLAVE DINÁMICA
            return back()->with('success', 'Mueble eliminado del carrito.');
        }

        return back()->with('error', 'Mueble no encontrado.');
    }

    /**
     * Vacía todo el carrito (Requerimiento 4.a).
     */
    public function clear()
    {
        Session::forget($this->getCarritoKey()); // CLAVE DINÁMICA
        return back()->with('success', 'Carrito vaciado correctamente.');
    }
}