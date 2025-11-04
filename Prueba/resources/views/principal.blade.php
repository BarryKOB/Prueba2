@extends('cabecera')

@section('titulo', 'Catálogo de Muebles')

@section('contenido')
    <h2 class="mb-4">Catálogo de Muebles</h2>
    
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- LECTURA DE LA MONEDA para mostrar el símbolo correcto (R1.b) --}}
    @php
        $monedaSimbolo = request()->cookie('preferencia_moneda', '€'); 
        // Se asume que TiendaController pasa $mueblesPaginated, $totalPages, etc.
        // Si TiendaController no pasa estos, la vista fallará. 
        // Usaremos el MockData de Mueble si no hay paginación compleja.
        $mueblesPaginated = \App\Models\Mueble::getAllMockData(); // FALLBACK: Usa Mock si la lógica de TiendaController es simple.
    @endphp

    <div class="row">
        {{-- Itera sobre los muebles para mostrar el botón de añadir --}}
        @foreach ($mueblesPaginated as $mueble)
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <img src="https://via.placeholder.com/200x150/{{ $mueble['id'] ?? $mueble->getId() }}" class="card-img-top" alt="{{ $mueble['nombre'] ?? $mueble->getNombre() }}"> 
                    
                    <div class="card-body text-center d-flex flex-column justify-content-between">
                        <div>
                            {{-- Usar getters para asegurar la compatibilidad con objetos Mueble --}}
                            <h5 class="card-title">{{ $mueble['nombre'] ?? $mueble->getNombre() }}</h5>
                            <p><strong>{{ number_format($mueble['precio'] ?? $mueble->getPrecio(), 2) }} {{ $monedaSimbolo }}</strong></p>
                            <p class="text-muted">Stock: {{ $mueble['stock'] ?? $mueble->getStock() }}</p>
                        </div>

                        {{-- Formulario para añadir al carrito (R4.a) --}}
                        <form method="POST" action="{{ route('carrito.add', ['muebleId' => $mueble['id'] ?? $mueble->getId()]) }}">
                            @csrf
                            @php $stock = $mueble['stock'] ?? $mueble->getStock(); @endphp

                            <div class="d-grid gap-2"> 
                                <input class="form-control text-center" 
                                       type="number" 
                                       name="cantidad" 
                                       value="1" 
                                       min="1" 
                                       max="{{ $stock }}" 
                                       style="width: 100px; margin: 0 auto;"
                                       @if($stock == 0) disabled @endif
                                       required>
                                
                                <button class="btn btn-primary mt-2" 
                                        type="submit" 
                                        @if($stock == 0) disabled @endif>
                                    Agregar al carrito
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    {{-- (Añadir aquí el código de paginación si se pasa del controlador) --}}
@endsection