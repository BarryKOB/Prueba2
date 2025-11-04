@extends('cabecera')

@section('titulo', 'Detalle mueble')

@section('contenido')
    <h2>Detalle de mueble</h2>
    <p><strong>Nombre:</strong> {{ $mueble->getNombre() }}</p>
    <p><strong>Descripcion:</strong> {{ $mueble->getDescripcion() ?? '-' }}</p>
    <br>
    <a href="{{ route('muebles.index') }}">Volver al listado</a>
@endsection
