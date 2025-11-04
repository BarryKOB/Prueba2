@extends('cabecera')

@section('titulo', 'Editar categoria')

@section('contenido')
    <h2>Editar categoria</h2>

    <form method="POST" action="{{ route('categorias.update', $categoria['id']) }}">
        @csrf
        @method('PUT')
        <input type="text" name="nombre" value="{{ $categoria['nombre'] }}" placeholder="nombre" required>
        <input type="text" name="descripcion" value="{{ $categoria['descripcion'] ?? '' }}" placeholder="descripcion">

        <button class="btn-guardar" type="submit">Actualizar</button>
    </form>

    <br>
    <a href="{{ url()->previous() }}" class="btn-volver">Volver</a>
@endsection
