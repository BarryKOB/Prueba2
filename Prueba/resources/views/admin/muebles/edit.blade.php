@extends('cabecera')

@section('titulo', 'Editar Mueble')

@section('contenido')
    <h2>Editar mueble</h2>

    <form action="{{ route('muebles.update', $mueble->getId()) }}" method="POST" class="mt-2">
    @csrf
    @method('PUT')
    <input type="text" name="nombre" value="{{ $mueble->getNombre() }}" required>
    <input type="number" step="0.1" name="precio" value="{{ $mueble->getPrecio() }}" required>
    <input type="number" step="0.1" name="stock" value="{{ $mueble->getStock() }}" required>
    <input type="text" name="materiales" value="{{ $mueble->getMateriales() }}" required>
    <input type="text" name="dimensiones" value="{{ $mueble->getDimensiones() }}" required>
    <input type="text" name="color_principal" value="{{ $mueble->getColorPrincipal() }}" required>
    <input type="text" name="destacado" value="{{ $mueble->getDestacado() }}" required>
    <button class="btn-guardar" type="submit">Actualizar</button>
</form>


    <form action="{{ route('mueble.gallery', $mueble->getId()) }}" method="GET" class="mt-2">
        <button type="submit" class="btn-editar">Editar Galeria</button>
    </form>

    <br>
    <a href="{{ url()->previous() }}" class="btn-volver">
        Volver
    </a>
@endsection
