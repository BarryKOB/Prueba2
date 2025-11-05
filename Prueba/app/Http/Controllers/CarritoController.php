<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\Mueble; 
use App\Models\User;

class CarritoController extends Controller
{
    // Claves de persistencia del carrito
    const COOKIE_MINUTES = 60 * 24 * 30;
    const ANONYMOUS_KEY = 'carrito_anonimo'; 

    private function getStorageIdentifier(): string
    {
        if (Session::has('usuario')) {
            $usuarioData = json_decode(Session::get('usuario'));
            // Ligamos el carrito al email del usuario (privado) - R4.c
            return 'carrito_user_' . md5($usuarioData->email); 
        }
        return self::ANONYMOUS_KEY;
    }

    private function getCarritoFromStorage(Request $request): array
    {
        $key = $this->getStorageIdentifier();
        if (Session::has($key)) { return Session::get($key, []); }

        $cookie_data = $request->cookie($key);
        if ($cookie_data) {
            $carrito = json_decode($cookie_data, true);
            Session::put($key, $carrito);
            return $carrito;
        }
        return [];
    }

    private function saveCarritoToStorage(array $carrito)
    {
        $key = $this->getStorageIdentifier();
        Session::put($key, $carrito); 
        Cookie::queue($key, json_encode($carrito), self::COOKIE_MINUTES);
    }
    
    /**
     * Función CRÍTICA: Actualiza el stock del mueble en su cookie individual.
     */
    private function updateMuebleStockInCookie(string $id, int $newStock)
    {
        $cookieName = "mueble_{$id}";
        $cookieData = json_decode(request()->cookie($cookieName), true); 
        
        // Si la cookie no existe (Admin nunca lo guardó), usamos el MockData completo como base
        if (!is_array($cookieData)) {
            $cookieData = Mueble::getAllMockData()[$id] ?? null;
            if (!$cookieData) return;
        }
        
        // Actualizamos el stock
        $cookieData['stock'] = $newStock;
        
        // Guardamos la cookie completa con el stock actualizado
        Cookie::queue($cookieName, json_encode($cookieData), self::COOKIE_MINUTES);
    }
    
    /**
     * Función auxiliar para obtener datos de un mueble (LEYENDO DE MOCK/COOKIE).
     * Esta función garantiza que obtenemos el stock actualizado.
     */
    private function getMuebleById(string $id): ?array
    {
        // 1. OBTENER LISTA DE MUEBLES (Mocks base + Muebles creados por Admin)
        $mueblesMock = Mueble::getAllMockData(); // Lista base (arrays)
        $mueblesAdmin = Session::get('muebles', []); // Muebles creados por el CRUD (Objetos Mueble)
        $mueblesCombined = array_merge($mueblesMock, $mueblesAdmin);
        
        // CRÍTICO: Si el mueble es un objeto (viene de Admin CRUD), usamos toArray() para unificar
        $mueble = $mueblesCombined[$id] ?? null;

        if ($mueble && $mueble instanceof Mueble) {
            // Si el mueble es un objeto, lo convertimos temporalmente a array para el manejo de stock
            $mueble = $mueble->jsonSerialize(); // Usamos jsonSerialize para obtener un array
        }
        
        // 2. SOBREESCRIBIR STOCK CON EL VALOR PERSISTIDO EN COOKIE
        if ($mueble) {
            $cookieData = request()->cookie("mueble_{$id}");
            if ($cookieData) {
                $arr = json_decode($cookieData, true);
                if (isset($arr['stock'])) {
                    // LÍNEA 88 CORREGIDA: Accedemos al array asociativo $mueble
                    $mueble['stock'] = $arr['stock']; 
                }
            }
        }
        return $mueble; // Devuelve el mueble como array (para usar en el add/update)
    }

    public function show(Request $request)
    {
        $carrito = $this->getCarritoFromStorage($request); 
        $subtotal = array_sum(array_map(fn($item) => $item['precio'] * $item['cantidad'], $carrito));
        $impuestos = $subtotal * 0.16;
        $total = $subtotal + $impuestos;

        return view('carrito.index', compact('carrito', 'subtotal', 'impuestos', 'total')); 
    }

    public function add(Request $request, string $muebleId)
    {
        $mueble = $this->getMuebleById($muebleId);
        if (!$mueble) { return back()->with('error', 'El mueble no existe.'); }

        $cantidadAAnadir = $request->input('cantidad', 1);
        $carrito = $this->getCarritoFromStorage($request); 
        $stockDisponible = $mueble['stock'];
        $cantidadActualEnCarrito = $carrito[$muebleId]['cantidad'] ?? 0;
        $nuevaCantidadTotalEnCarrito = $cantidadActualEnCarrito + $cantidadAAnadir;

        // Validación de Stock (R4.d)
        if ($nuevaCantidadTotalEnCarrito > $stockDisponible) {
            return back()->with('error', 'No hay suficiente stock. Stock disponible: ' . $stockDisponible);
        }

        // DISMINUIR STOCK EN LA COOKIE DEL MUEBLE
        $stockFinal = $stockDisponible - $cantidadAAnadir;
        $this->updateMuebleStockInCookie($muebleId, $stockFinal);

        if (isset($carrito[$muebleId])) {
            $carrito[$muebleId]['cantidad'] = $nuevaCantidadTotalEnCarrito;
        } else {
            $carrito[$muebleId] = [
                'mueble_id' => $muebleId, 'nombre' => $mueble['nombre'], 'precio' => $mueble['precio'], 
                'cantidad' => $cantidadAAnadir, 'stock_disponible' => $stockDisponible,
            ];
        }

        $this->saveCarritoToStorage($carrito); 
        return redirect()->route('carrito.show')->with('success', $mueble['nombre'] . ' añadido al carrito.');
    }

    public function update(Request $request, string $muebleId)
    {
        $request->validate(['cantidad' => 'required|integer|min:1']);
        $cantidadNueva = $request->input('cantidad');
        $carrito = $this->getCarritoFromStorage($request); 

        if (isset($carrito[$muebleId])) {
            $stockDisponible = $carrito[$muebleId]['stock_disponible'];
            $cantidadVieja = $carrito[$muebleId]['cantidad'];
            
            if ($cantidadNueva > $stockDisponible) {
                return back()->with('error', 'No hay suficiente stock. Máximo: ' . $stockDisponible);
            }
            
            // AJUSTE DE STOCK
            $diferenciaNeta = $cantidadNueva - $cantidadVieja; 
            $stockMuebleActual = $this->getMuebleById($muebleId)['stock'];
            $nuevoStockMueble = $stockMuebleActual - $diferenciaNeta;

            $this->updateMuebleStockInCookie($muebleId, $nuevoStockMueble); 

            $carrito[$muebleId]['cantidad'] = $cantidadNueva;
            $this->saveCarritoToStorage($carrito); 
            return back()->with('success', 'Cantidad actualizada.');
        }

        return back()->with('error', 'Mueble no encontrado en el carrito.');
    }

    public function remove(string $muebleId, Request $request)
    {
        $carrito = $this->getCarritoFromStorage($request); 

        if (isset($carrito[$muebleId])) {
            $cantidadDevuelta = $carrito[$muebleId]['cantidad'];
            
            // RESTAURAR STOCK
            $stockMuebleActual = $this->getMuebleById($muebleId)['stock'];
            $nuevoStockMueble = $stockMuebleActual + $cantidadDevuelta; 
            $this->updateMuebleStockInCookie($muebleId, $nuevoStockMueble);

            unset($carrito[$muebleId]);
            $this->saveCarritoToStorage($carrito); 
            return back()->with('success', 'Mueble eliminado del carrito.');
        }

        return back()->with('error', 'Mueble no encontrado.');
    }

    public function clear(Request $request)
    {
        $carrito = $this->getCarritoFromStorage($request); 

        // RESTAURACIÓN DE STOCK PARA TODOS LOS ÍTEMS
        foreach ($carrito as $muebleId => $item) {
             $cantidadDevuelta = $item['cantidad'];
             
             $muebleActual = $this->getMuebleById($muebleId); 
             if ($muebleActual) {
                 $stockMuebleActual = $muebleActual['stock'];
                 $nuevoStockMueble = $stockMuebleActual + $cantidadDevuelta;
                 $this->updateMuebleStockInCookie($muebleId, $nuevoStockMueble);
             }
        }

        $key = $this->getStorageIdentifier();

        Session::forget($key);
        Cookie::queue(Cookie::forget($key)); 
        return back()->with('success', 'Carrito vaciado correctamente.');
    }
}