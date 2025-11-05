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
        // 1. Comprobación de Sesión Activa
        if (!Session::has('autorizacion_usuario') || !Session::get('autorizacion_usuario')) {
            abort(403, 'Acceso no autorizado. Debe iniciar sesión.');
        }

        // 2. Obtener y Decodificar la Sesión de Usuario de forma segura
        $usuarioJson = Session::get('usuario');
        if (!$usuarioJson) {
             abort(403, 'Acceso denegado. Datos de usuario faltantes.'); 
        }
        $usuarioData = json_decode($usuarioJson);

        // 3. VERIFICACIÓN DE ROL: CRÍTICO PARA R5/R6
        // Comparamos contra la cadena de valor simple 'admin'
        if (!isset($usuarioData->rol) || $usuarioData->rol !== 'admin') {
            abort(403, 'Acceso denegado. Se requiere rol de Administrador.');
        }
    }


    public function index()
    {
        // Si el login falla o el rol es incorrecto, el requireLogin aborta.
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



     public function gallery(string $id)
    {
        $this->requireLogin();
        $muebles = Session::get('muebles', []);
        if (!isset($muebles[$id])) abort(404);

        $mueble = $this->toMueble($muebles[$id]);

        return view('admin.muebles.galeria', compact('mueble'));
    }

    public function galleryUpload(Request $request, string $id)
    {
        $this->requireLogin();
        $muebles = Session::get('muebles', []);
        if (!isset($muebles[$id])) abort(404);

        // i. Validación de subida múltiple (tipos y tamaño)
        $request->validate([
            'imagen' => 'required|array',
            'imagen.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Máximo 2MB por imagen
        ]);

        $mueble = $this->toMueble($muebles[$id]);
        $imagenes = $mueble->getImagenes() ?? [];

        if ($request->hasFile('imagen')) {
            foreach ($request->file('imagen') as $file) {
                // Genera un nombre único y guarda el archivo en 'storage/app/public/muebles/{id}'
                $path = $file->store("muebles/{$id}", 'public');
                $imagenes[] = $path;
            }
        }

        $mueble->setImagenes($imagenes);
        $muebles[$id] = $mueble;
        Session::put('muebles', $muebles);

        // Actualizamos la cookie para que el catálogo refleje las nuevas imágenes
        $minutes = 60 * 24 * 30;
        $payload = $mueble->jsonSerialize();
        Cookie::queue("mueble_{$id}", json_encode($payload, JSON_UNESCAPED_UNICODE), $minutes);

        return redirect()->route('mueble.gallery', $id)->with('success', 'Imágenes subidas correctamente.');
    }

    public function imagen(string $id, string $imagen)
    {
        // Este método sirve la imagen de forma segura desde el storage.     
        $path = "muebles/{$id}/{$imagen}";

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    public function galleryDelete(string $id, string $imagen)
    {
        $this->requireLogin();
        $muebles = Session::get('muebles', []);
        if (!isset($muebles[$id])) abort(404);

        $mueble = $this->toMueble($muebles[$id]);
        $imagenes = $mueble->getImagenes() ?? [];

        $pathCompleto = "muebles/{$id}/{$imagen}";
        $imagenesFiltradas = [];

        // iii. Borrado de imágenes
        foreach ($imagenes as $imgPath) {
            // Comparamos el nombre base del archivo para encontrar la imagen a borrar
            if (basename($imgPath) === $imagen) {
                // Borramos el archivo físico del disco
                Storage::disk('public')->delete($imgPath);
            } else {
                $imagenesFiltradas[] = $imgPath;
            }
        }

        $mueble->setImagenes($imagenesFiltradas);
        $muebles[$id] = $mueble;
        Session::put('muebles', $muebles);

        // Actualizamos la cookie
        $minutes = 60 * 24 * 30;
        $payload = $mueble->jsonSerialize();
        Cookie::queue("mueble_{$id}", json_encode($payload, JSON_UNESCAPED_UNICODE), $minutes);

        return redirect()->route('mueble.gallery', $id)->with('success', 'Imagen eliminada correctamente.');
    }
}