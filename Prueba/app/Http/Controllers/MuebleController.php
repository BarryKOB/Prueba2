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

    // ... dentro de MuebleController.php ...

    private function requireLogin()
    {
        if (!Session::has('autorizacion_usuario') || !Session::get('autorizacion_usuario')) {
            abort(403, 'Acceso no autorizado. Debe iniciar sesión.');
        }
        
        $usuarioData = json_decode(Session::get('usuario'));

        // VERIFICACIÓN DE ROL: (Requerimiento 5 y 6)
        if (!isset($usuarioData->rol) || $usuarioData->rol !== \App\Enums\RolUser::ADMIN) {
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
        $muebles[$id] = new Mueble(
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
        Session::put('muebles', $muebles);

        $minutes = 60 * 24 * 30;
        $payload = [
            'id' => $id,
            'nombre' => $data['nombre'],
            'categoria_id' => $categoriaIds,
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'stock' => $data['stock'],
            'materiales' => $data['materiales'] ?? null,
            'dimensiones' => $data['dimensiones'] ?? null,
            'color_principal' => $data['color_principal'] ?? null,
            'destacado' => $data['destacado'] ?? false,
        ];
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

        $muebles[$id] = new Mueble(
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
        Session::put('muebles', $muebles);

        $minutes = 60 * 24 * 30;
        $payload = [
            'id' => $id,
            'nombre' => $data['nombre'],
            'categoria_id' => $categoriaIds,
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'stock' => $data['stock'],
            'materiales' => $data['materiales'] ?? null,
            'dimensiones' => $data['dimensiones'] ?? null,
            'color_principal' => $data['color_principal'] ?? null,
            'destacado' => $data['destacado'] ?? false,
        ];
        Cookie::queue("mueble_{$id}", json_encode($payload, JSON_UNESCAPED_UNICODE), $minutes);

        return redirect()->route('muebles.index');
    }

    public function destroy(string $id)
    {
        $this->requireLogin();
        $muebles = Session::get('muebles', []);
        unset($muebles[$id]);
        Session::put('muebles', $muebles);

        Cookie::queue(Cookie::forget("mueble_{$id}"));

        return redirect()->route('muebles.index');
    }

    // --- Galería de imágenes (opcional, si usas la vista y rutas) ---

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

        $request->validate([
            'imagen' => 'required',
            'imagen.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $muebles = Session::get('muebles', []);
        if (!isset($muebles[$id])) abort(404);

        $mueble = $this->toMueble($muebles[$id]);
        $imagenes = $mueble->getImagenes() ?? [];

        if ($request->hasFile('imagen')) {
            foreach ($request->file('imagen') as $file) {
                $safeName = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                $path = Storage::putFileAs('muebles/' . $id, $file, $safeName);
                $imagenes[] = $path;
            }
        }

        $mueble->setImagenes($imagenes);
        $muebles[$id] = $mueble;
        Session::put('muebles', $muebles);

        return redirect()->route('mueble.gallery', $id)->with('mensaje', 'Imágenes subidas correctamente');
    }

    public function imagen(string $id, string $imagen)
    {
        $this->requireLogin();

        $imagen = basename($imagen);
        $path = 'muebles/' . $id . '/' . $imagen;

        if (!Storage::exists($path)) {
            abort(404);
        }

        return Storage::response($path);
    }

    public function galleryDelete(string $id, string $imagen)
    {
        $this->requireLogin();

        $imagen = basename($imagen);
        $path = 'muebles/' . $id . '/' . $imagen;

        $muebles = Session::get('muebles', []);
        if (!isset($muebles[$id])) abort(404);

        $mueble = $this->toMueble($muebles[$id]);
        $imagenes = $mueble->getImagenes() ?? [];

        if (Storage::exists($path)) {
            Storage::delete($path);
        }

        $imagenes = array_values(array_filter($imagenes, function ($p) use ($imagen) {
            return basename($p) !== $imagen;
        }));

        $mueble->setImagenes($imagenes);
        $muebles[$id] = $mueble;
        Session::put('muebles', $muebles);

        return redirect()->route('mueble.gallery', $id)->with('mensaje', 'Imagen eliminada');
    }
}
