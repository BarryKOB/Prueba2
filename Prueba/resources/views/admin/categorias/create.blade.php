@extends('cabecera')

@section('titulo', 'Nueva categoria')

@section('contenido')
    <h2>Nueva categoria</h2>

    <form method="POST" action="{{ route('categorias.store') }}">
        @csrf
        <input type="text" name="nombre" placeholder="nombre" required>
        <input type="text" name="descripcion" placeholder="descripcion">

        <button class="btn-guardar" type="submit">Guardar categoria</button>
    </form>
    <br>
    <a href="{{ url()->previous() }}" class="btn-volver">Volver</a>
@endsection