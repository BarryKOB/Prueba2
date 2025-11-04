@extends('cabecera')

@section('titulo', 'Listado de muebles')

@section('contenido')
    <h1>Muebles disponibles</h1>

    @if (empty($muebles))
        <p>No hay muebles disponibles.</p>
    @else
        <div class="muebles-lista">
            @foreach ($muebles as $mueble)
                <div class="mueble-card">
                    <h3>
                        @if (Route::has('tienda.show'))
                            <a href="{{ route('tienda.show', $mueble->getId()) }}">{{ $mueble->getNombre() }}</a>
                        @else
                            {{ $mueble->getNombre() }}
                        @endif
                    </h3>
                    <p><strong>Precio:</strong> {{ $mueble->getPrecio() }} €</p>
                    <p><strong>Descripción:</strong> {{ $mueble->getDescripcion() ?? '-' }}</p>
                </div>
            @endforeach
        </div>
    @endif
@endsection
