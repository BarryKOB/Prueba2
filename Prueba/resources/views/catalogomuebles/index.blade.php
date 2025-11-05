@extends('cabecera')

@section('titulo', 'Catálogo de Muebles')

@section('contenido')
    {{-- CRÍTICO: El TiendaController debe pasar estas variables --}}
    @php
        $mueblesPaginated = $mueblesPaginated ?? [];
        $monedaSimbolo = $monedaSimbolo ?? '€'; 
        $totalItems = $totalItems ?? 0;
        $page = $page ?? 1;
        $totalPages = $totalPages ?? 1;
        $currentQuery = $currentQuery ?? [];
    @endphp

    <h2 class="mb-4">Catálogo de Muebles ({{ $totalItems }} ítems)</h2>
    
    {{-- Mensajes flash --}}
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    @if (empty($mueblesPaginated))
        <div class="alert alert-info">No hay muebles disponibles.</div>
    @else
        <div class="row">
            @foreach ($mueblesPaginated as $mueble)
                <div class="col-md-3 mb-4">
                    <div class="mueble-card card h-100 p-3 text-center">
                        @if ($mueble->getImagenes() && !empty($mueble->getImagenes()))
                            @php $primeraImagen = basename($mueble->getImagenes()[0]); @endphp
                            <img src="{{ route('mueble.imagen', ['id' => $mueble->getId(), 'imagen' => $primeraImagen]) }}"
                                 class="card-img-top" alt="{{ $mueble->getNombre() }}" style="height: 150px; object-fit: cover;">
                        @else
                            <img src="https://via.placeholder.com/200x150/{{ $mueble->getId() }}" class="card-img-top" alt="{{ $mueble->getNombre() }}">
                        @endif
                        
                        <div class="card-body text-center d-flex flex-column justify-content-between">
                            <div>
                                {{-- Nombre con enlace a Detalle --}}
                                <h5 class="card-title">
                                    <a href="{{ route('catalogomuebles.show', $mueble->getId()) }}">{{ $mueble->getNombre() }}</a>
                                </h5>
                                {{-- Precio con Moneda Dinámica (R1.b) --}}
                                <p><strong>{{ number_format($mueble->getPrecio(), 2) }} {{ $monedaSimbolo }}</strong></p>
                                <p class="text-muted">Stock: {{ $mueble->getStock() }}</p>
                            </div>

                            {{-- FORMULARIO DE AÑADIR AL CARRITO (R4.a) --}}
                            <form method="POST" action="{{ route('carrito.add', ['muebleId' => $mueble->getId()]) }}" class="d-grid gap-2 mt-2">
                                @csrf
                                @php $stock = $mueble->getStock(); @endphp

                                <input class="form-control text-center" 
                                       type="number" 
                                       name="cantidad" 
                                       value="1" 
                                       min="1" 
                                       max="{{ $stock }}" 
                                       style="width: 100px; margin: 0 auto;">
                                
                                <button type="submit" class="btn btn-primary" @if($stock == 0) disabled @endif>
                                    Agregar al carrito
                                </button>

                                <a href="{{ route('catalogomuebles.show', $mueble->getID()) }}">Ver</a>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Controles de Paginación Manual (R1.c, R3.b.iii) --}}
        @if ($totalPages > 1)
            <nav aria-label="Paginación">
                <ul class="pagination justify-content-center">
                    {{-- Botón Anterior --}}
                    <li class="page-item @if($page <= 1) disabled @endif">
                        {{-- Los enlaces preservan los filtros/querys existentes --}}
                        <a class="page-link" href="{{ route('catalogomuebles.index', array_merge($currentQuery, ['page' => $page - 1])) }}">Anterior</a>
                    </li>
                    
                    {{-- Números de página --}}
                    @for ($i = 1; $i <= $totalPages; $i++)
                        <li class="page-item @if($i == $page) active @endif">
                            <a class="page-link" href="{{ route('catalogomuebles.index', array_merge($currentQuery, ['page' => $i])) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    {{-- Botón Siguiente --}}
                    <li class="page-item @if($page >= $totalPages) disabled @endif">
                        <a class="page-link" href="{{ route('catalogomuebles.index', array_merge($currentQuery, ['page' => $page + 1])) }}">Siguiente</a>
                    </li>
                </ul>
            </nav>
        @endif
    @endif
@endsection
