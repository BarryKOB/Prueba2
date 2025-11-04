@extends('cabecera')

@section('titulo', $mueble->getNombre())

@section('contenido')
    <h2>{{ $mueble->getNombre() }}</h2>
    <p><strong>Descripción:</strong> {{ $mueble->getDescripcion() ?? '-' }}</p>
    <p><strong>Precio:</strong> {{ $mueble->getPrecio() }} €</p>
    <p><strong>Stock:</strong> {{ $mueble->getStock() }}</p>
    <p><strong>Materiales:</strong> {{ $mueble->getMateriales() ?? '-' }}</p>
    <p><strong>Dimensiones:</strong> {{ $mueble->getDimensiones() ?? '-' }}</p>
    <p><strong>Color principal:</strong> {{ $mueble->getColorPrincipal() ?? '-' }}</p>
    <p><strong>Destacado:</strong> {{ $mueble->getDestacado() ? 'Sí' : 'No' }}</p>

    @php
        $seleccionadas = $mueble->getCategoria() ?? [];
        $categorias = session('categorias', []);
    @endphp

    <h3>Categorías</h3>
    @if (!empty($seleccionadas) && !empty($categorias))
        <ul>
            @foreach ($categorias as $cat)
                @if (in_array($cat['id'], $seleccionadas))
                    <li>{{ $cat['nombre'] }}</li>
                @endif
            @endforeach
        </ul>
    @else
        <p>Sin categoría</p>
    @endif

    <br>
    <a href="{{ url()->previous() }}" class="btn-volver">Volver</a>
@endsection
