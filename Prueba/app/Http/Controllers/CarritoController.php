<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\User; // Asumimos que User está disponible

class CarritoController extends Controller
{
    // Claves de persistencia del carrito
    const COOKIE_MINUTES = 60 * 24 * 30; // 30 días de persistencia
    const ANONYMOUS_KEY = 'carrito_anonimo'; 

    /**
     * Determina el identificador único para el carrito.
     * Si hay un usuario logueado, usa su email (privado).
     * Si no hay login, usa una clave anónima (pública para el navegador).
     */
    private function getStorageIdentifier(): string
    {
        if (Session::has('usuario')) {
            $usuarioData = json_decode(Session::get('usuario'));
            // Ligamos el carrito al email del usuario (privado)
            return 'carrito_user_' . md5($usuarioData->email); 
        }
        // Clave anónima ligada a la sesión (pública)
        return self::ANONYMOUS_KEY;
    }

    /**
     * Función auxiliar para obtener el carrito, priorizando la sesión, luego la cookie (R4.c).
     */
    private function getCarritoFromStorage(Request $request): array
    {
        $key = $this->getStorageIdentifier();

        // 1. Prioridad 1: Leer de la SESIÓN (siempre más reciente)
        if (Session::has($key)) {
            return Session::get($key, []);
        }

        // 2. Prioridad 2: Leer de la COOKIE (Persistencia post-logout/cierre de navegador)
        $cookie_data = $request->cookie($key);
        if ($cookie_data) {
            $carrito = json_decode($cookie_data, true);
            // Cargar la cookie a la sesión tan pronto como se lee
            Session::put($key, $carrito);
            return $carrito;
        }

        return [];
    }

    /**
     * Función auxiliar para guardar el carrito en la Sesión y en la Cookie (R4.c).
     */
    private function saveCarritoToStorage(array $carrito)
    {
        $key = $this->getStorageIdentifier();

        // Guardar en Sesión (para acceso rápido durante la navegación)
        Session::put($key, $carrito); 

        // Guardar en Cookie (para Persistencia post-logout/cierre de navegador)
        Cookie::queue($key, json_encode($carrito), self::COOKIE_MINUTES);
    }

    /**
     * Función auxiliar para obtener datos de un mueble (Mock Data).
     */
    private function getMuebleById(string $id): ?array
    {
        // ... (Tu Mock Data) ...
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
    public function show(Request $request)
    {
        $carrito = $this->getCarritoFromStorage($request); 
        
        $subtotal = array_sum(array_map(fn($item) => $item['precio'] * $item['cantidad'], $carrito));
        $impuestos = $subtotal * 0.16;
        $total = $subtotal + $impuestos;

        return view('carrito.show', compact('carrito', 'subtotal', 'impuestos', 'total'));
    }

    public function add(Request $request, string $muebleId)
    {
        $mueble = $this->getMuebleById($muebleId);
        if (!$mueble) { return back()->with('error', 'El mueble no existe.'); }

        $cantidadAAnadir = $request->input('cantidad', 1);
        $carrito = $this->getCarritoFromStorage($request); 
        $stockDisponible = $mueble['stock'];
        $cantidadActual = $carrito[$muebleId]['cantidad'] ?? 0;
        $nuevaCantidad = $cantidadActual + $cantidadAAnadir;

        // Validación de Stock (R4.d)
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

        $this->saveCarritoToStorage($carrito); 
        return redirect()->route('carrito.show')->with('success', $mueble['nombre'] . ' añadido al carrito.');
    }

    public function update(Request $request, string $muebleId)
    {
        $request->validate(['cantidad' => 'required|integer|min:1']);
        $cantidad = $request->input('cantidad');
        $carrito = $this->getCarritoFromStorage($request); 

        if (isset($carrito[$muebleId])) {
            $stockDisponible = $carrito[$muebleId]['stock_disponible'];
            if ($cantidad > $stockDisponible) {
                return back()->with('error', 'No hay suficiente stock. Máximo: ' . $stockDisponible);
            }

            $carrito[$muebleId]['cantidad'] = $cantidad;
            $this->saveCarritoToStorage($carrito); 
            return back()->with('success', 'Cantidad actualizada.');
        }

        return back()->with('error', 'Mueble no encontrado en el carrito.');
    }

    public function remove(string $muebleId, Request $request)
    {
        $carrito = $this->getCarritoFromStorage($request); 

        if (isset($carrito[$muebleId])) {
            unset($carrito[$muebleId]);
            $this->saveCarritoToStorage($carrito); 
            return back()->with('success', 'Mueble eliminado del carrito.');
        }

        return back()->with('error', 'Mueble no encontrado.');
    }

    public function clear(Request $request)
    {
        $key = $this->getStorageIdentifier();

        // Limpiamos la Sesión
        Session::forget($key);
        // Borramos la cookie 
        Cookie::queue(Cookie::forget($key)); 
        return back()->with('success', 'Carrito vaciado correctamente.');
    }
}