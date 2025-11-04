<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CategoriaController extends Controller
{
    // FUNCIÓN DE VERIFICACIÓN DE ROL Y LOGIN (Requerimiento 5 y 6)
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
        $categorias = Session::get('categorias', []);
        return view('admin.categorias.index', compact('categorias'));
    }

    public function create()
    {
        $this->requireLogin();
        return view('admin.categorias.create');
    }

    public function store(Request $request)
    {
        $this->requireLogin();
        $data = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
        ]);

        $id = uniqid();
        $categorias = Session::get('categorias', []);
        $categorias[$id] = array_merge(['id' => $id], $data);
        Session::put('categorias', $categorias);

        return redirect()->route('categorias.index');
    }

    public function show(string $id)
    {
        $this->requireLogin();
        $categorias = Session::get('categorias', []);
        if (!isset($categorias[$id])) abort(404);
        $categoria = $categorias[$id];
        return view('admin.categorias.show', compact('categoria'));
    }

    public function edit(string $id)
    {
        $this->requireLogin();
        $categorias = Session::get('categorias', []);
        if (!isset($categorias[$id])) abort(404);
        $categoria = $categorias[$id];
        return view('admin.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, string $id)
    {
        $this->requireLogin();
        $categorias = Session::get('categorias', []);
        if (!isset($categorias[$id])) abort(404);

        $data = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
        ]);

        $categorias[$id] = array_merge(['id' => $id], $data);
        Session::put('categorias', $categorias);

        return redirect()->route('categorias.index');
    }

    public function destroy(string $id)
    {
        $this->requireLogin();
        $categorias = Session::get('categorias', []);
        unset($categorias[$id]);
        Session::put('categorias', $categorias);
        return redirect()->route('categorias.index');
    }
}