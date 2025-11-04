@extends('cabecera')

@section('titulo', 'Detalle categoria')

@section('contenido')
    <h2>Detalle de categoria</h2>
    <p><strong>Nombre:</strong> {{ $categoria['nombre'] }}</p>
    <p><strong>Descripcion:</strong> {{ $categoria['descripcion'] ?? '-' }}</p>
    <br>
    <a href="{{ route('categorias.index') }}">Volver al listado</a>
@endsection

