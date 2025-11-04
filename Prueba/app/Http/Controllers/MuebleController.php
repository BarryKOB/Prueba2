<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use App\Models\Mueble;

class MuebleController extends Controller
{
    private function toMueble($data): Mueble
    {
        if ($data instanceof Mueble) {
            return $data;
        }
        return new Mueble(
            $data['id'] ?? uniqid(),
            $data['nombre'] ?? '',
            $data['categoria_id'] ?? [],
            $data['descripcion'] ?? null,
            $data['precio'] ?? 0,
            $data['stock'] ?? 0,
            $data['materiales'] ?? null,
            $data['dimensiones'] ?? null,
            $data['color_principal'] ?? null,
            $data['destacado'] ?? false,
            $data['imagenes'] ?? []
        );
    }

    private function requireLogin()
    {
        if (!Session::has('autorizacion_usuario') || !Session::get('autorizacion_usuario')) {
            abort(403, 'Acceso no autorizado. Debe iniciar sesión.');
        }

        $usuarioData = json_decode(Session::get('usuario'));

        // VERIFICACIÓN DE ROL: CRÍTICO PARA R5/R6
        if (!isset($usuarioData->rol) || $usuarioData->rol !== \App\Enums\RolUser::ADMIN->value) {
            abort(403, 'Acceso denegado. Se requiere rol de Administrador.');
        }
    }

    public function index()
    {
        $this->requireLogin();
        $raw = Session::get('muebles', []);
        $muebles = [];
        foreach ($raw as $id => $mueble) {
            $muebles[$id] = $this->toMueble($mueble);
        }
        return view('admin.muebles.index', compact('muebles'));
    }

    public function create()
    {
        $this->requireLogin();
        $categorias = Session::get('categorias', []);
        return view('admin.muebles.create', compact('categorias'));
    }

    /**
     * Guarda el mueble en Session y en Cookie (para el Catálogo).
     */
    public function store(Request $request)
    {
        $this->requireLogin();

        $data = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'materiales' => 'nullable|string',
            'dimensiones' => 'nullable|string',
            'color_principal' => 'nullable|string',
            'categoria_id' => 'nullable|array',
            'categoria_id.*' => 'string',
        ]);
        $data['destacado'] = $request->boolean('destacado');

        $id = uniqid();
        $categoriaIds = (array) $request->input('categoria_id', []);

        $muebles = Session::get('muebles', []);
        $muebleInstance = new Mueble(
            $id,
            $data['nombre'],
            $categoriaIds,
            $data['descripcion'] ?? null,
            $data['precio'],
            $data['stock'],
            $data['materiales'] ?? null,
            $data['dimensiones'] ?? null,
            $data['color_principal'] ?? null,
            $data['destacado'] ?? false,
            []
        );
        $muebles[$id] = $muebleInstance;
        Session::put('muebles', $muebles);

        // CRÍTICO: Guardar inmediatamente en Cookie para que TiendaController pueda leer el catálogo.
        $minutes = 60 * 24 * 30;
        $payload = $muebleInstance->jsonSerialize();
        Cookie::queue("mueble_{$id}", json_encode($payload, JSON_UNESCAPED_UNICODE), $minutes);

        return redirect()->route('muebles.index');
    }

    public function show(string $id)
    {
        $this->requireLogin();
        $muebles = Session::get('muebles', []);
        if (!isset($muebles[$id])) abort(404);
        $mueble = $this->toMueble($muebles[$id]);
        return view('admin.muebles.show', compact('mueble'));
    }

    public function edit(string $id)
    {
        $this->requireLogin();
        $muebles = Session::get('muebles', []);
        if (!isset($muebles[$id])) abort(404);
        $mueble = $this->toMueble($muebles[$id]);
        $categorias = Session::get('categorias', []);
        return view('admin.muebles.edit', compact('mueble', 'categorias'));
    }

    public function update(Request $request, string $id)
    {
        $this->requireLogin();

        $muebles = Session::get('muebles', []);
        if (!isset($muebles[$id])) abort(404);

        $data = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'materiales' => 'nullable|string',
            'dimensiones' => 'nullable|string',
            'color_principal' => 'nullable|string',
            'categoria_id' => 'nullable|array',
            'categoria_id.*' => 'string',
        ]);
        $data['destacado'] = $request->boolean('destacado');

        $categoriaIds = (array) $request->input('categoria_id', []);
        $prev = $this->toMueble($muebles[$id]);
        $imagenes = $prev->getImagenes() ?? [];

        $muebleInstance = new Mueble(
            $id,
            $data['nombre'],
            $categoriaIds,
            $data['descripcion'] ?? null,
            $data['precio'],
            $data['stock'],
            $data['materiales'] ?? null,
            $data['dimensiones'] ?? null,
            $data['color_principal'] ?? null,
            $data['destacado'] ?? false,
            $imagenes
        );
        $muebles[$id] = $muebleInstance;
        Session::put('muebles', $muebles);

        // CRÍTICO: Actualizar la Cookie después de la edición para que el catálogo lo refleje.
        $minutes = 60 * 24 * 30;
        $payload = $muebleInstance->jsonSerialize();
        Cookie::queue("mueble_{$id}", json_encode($payload, JSON_UNESCAPED_UNICODE), $minutes);

        return redirect()->route('muebles.index');
    }

    public function destroy(string $id)
    {
        $this->requireLogin();
        $muebles = Session::get('muebles', []);
        unset($muebles[$id]);
        Session::put('muebles', $muebles);

        // CRÍTICO: Eliminar la Cookie para que no persista en el catálogo.
        Cookie::queue(Cookie::forget("mueble_{$id}"));

        return redirect()->route('muebles.index');
    }

    // --- Métodos de galería (se mantienen sin modificar si no son tu responsabilidad) ---
    public function gallery(string $id) { /* ... */ }
    public function galleryUpload(Request $request, string $id) { /* ... */ }
    public function imagen(string $id, string $imagen) { /* ... */ }
    public function galleryDelete(string $id, string $imagen) { /* ... */ }
}