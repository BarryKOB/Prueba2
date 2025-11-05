@extends('cabecera')

@section('titulo', 'Detalle mueble')

@section('contenido')
    <h2>Detalle de mueble</h2>
    <p><strong>Nombre:</strong> {{ $mueble->getNombre() }}</p>
    <p><strong>Precio:</strong> {{ $mueble->getPrecio() }}€</p>
    <p><strong>Stock:</strong> {{ $mueble->getStock() }}</p>
    <p><strong>Materiales:</strong> {{ $mueble->getMateriales() }}</p>
    <p><strong>Dimensiones:</strong> {{ $mueble->getDimensiones() }}</p>
    <p><strong>Color Principal:</strong> {{ $mueble->getColorPrincipal() }}</p>
    <p><strong>Destacado:</strong> {{ $mueble->getDestacado()  ? 'Sí' : 'No' }}</p>
    <br>
    <a href="{{ route('catalogomuebles.index') }}">Volver al catálogo</a>
@endsection
