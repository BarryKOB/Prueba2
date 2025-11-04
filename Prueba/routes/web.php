<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\MuebleController;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\PreferenciasController;
use App\Http\Controllers\TiendaController;

Route::get(uri: '/', action: fn() => redirect()->route('principal'));

Route::get('/login', [LoginController::class, 'mostrar'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'cerrarSesion'])->name('logout');

Route::get('/dashboard', [AdministracionController::class, 'index'])->name('dashboard');
Route::get('/principal', [AdministracionController::class, 'principal'])->name('principal');

Route::get('/ver_sesion', function () {
    return Session::all();
});

// CRUDs DE ADMINISTRACIÓN
Route::resource('categorias', CategoriaController::class);
Route::resource('muebles', MuebleController::class);

// RUTAS DE GALERÍA Y TIENDA
Route::get('/muebles/{id}/galeria', [MuebleController::class, 'gallery'])->name('mueble.gallery');
Route::post('/muebles/{id}/galeria', [MuebleController::class, 'galleryUpload'])->name('mueble.gallery.upload');
Route::get('/muebles/{id}/imagen/{imagen}', [MuebleController::class, 'imagen'])->name('mueble.imagen');
Route::delete('/muebles/{id}/galeria/{imagen}', [MuebleController::class, 'galleryDelete'])->name('mueble.gallery.delete');
Route::get('/catalogo-muebles', [TiendaController::class, 'index'])->name('catalogomuebles.index');
Route::get('/catalogo-muebles/{id}', [TiendaController::class, 'show'])->name('catalogomuebles.show');

// RUTAS DE PREFERENCIAS (COOKIES)
Route::get('/preferencias', [PreferenciasController::class, 'edit'])->name('preferencias.edit');
Route::post('/preferencias', [PreferenciasController::class, 'update'])->name('preferencias.update');

// RUTAS DEL CARRITO (TU PARTE 4)
Route::get('/carrito', [CarritoController::class, 'show'])->name('carrito.show');
Route::post('/carrito/insertar/{muebleId}', [CarritoController::class, 'add'])->name('carrito.add');
Route::post('/carrito/actualizar/{muebleId}', [CarritoController::class, 'update'])->name('carrito.update');
Route::post('/carrito/eliminar/{muebleId}', [CarritoController::class, 'remove'])->name('carrito.remove');
Route::post('/carrito/vaciar', [CarritoController::class, 'clear'])->name('carrito.clear');