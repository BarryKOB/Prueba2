@extends('cabecera')

@section('titulo', 'Listado de muebles')

@section('contenido')
    <h1>Muebles disponibles</h1>
    {{-- Mensajes flash --}}
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    
    @php
        // Asume que TiendaController pasa $muebles (Objetos Mueble con stock actualizado) y $monedaSimbolo
        $muebles = $muebles ?? [];
        $monedaSimbolo = request()->cookie('preferencia_moneda', '€'); // Fallback para moneda
    @endphp

    @if (empty($muebles))
        <p>No hay muebles disponibles.</p>
    @else
        <div class="muebles-lista row">
            @foreach ($muebles as $mueble)
                <div class="col-md-3 mb-4">
                    <div class="mueble-card card h-100 p-3 text-center">
                        {{-- Contenido del compañero: Nombre, Precio, Stock --}}
                        <h3><a href="{{ route('catalogomuebles.show', $mueble->getId()) }}">{{ $mueble->getNombre() }}</a></h3>
                        <p><strong>Precio:</strong> {{ number_format($mueble->getPrecio(), 2) }} {{ $monedaSimbolo }}</p>
                        <p><strong>Stock:</strong> {{ $mueble->getStock() }}</p>

                        {{-- FORMULARIO CRÍTICO DE TU PARTE (R4.a) --}}
                        <form method="POST" action="{{ route('carrito.add', ['muebleId' => $mueble->getId()]) }}" class="d-grid gap-2 mt-2">
                            @csrf
                            @php $stock = $mueble->getStock(); @endphp

                            <input class="form-control text-center" 
                                   type="number" 
                                   name="cantidad" 
                                   value="1" 
                                   min="1" 
                                   max="{{ $stock }}" 
                                   style="width: 100px; margin: 0 auto;"
                                   @if($stock == 0) disabled @endif
                                   required>
                            
                            <button type="submit" class="btn btn-primary mt-2" @if($stock == 0) disabled @endif>
                                Agregar al carrito
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection