@extends('cabecera')

@section('contenido')
    @php
        // 1. Lógica de Preferencias (Lectura de Cookies R1.b, R1.c)
        $itemsPerPage = (int)request()->cookie('preferencia_paginacion', 12); // Paginación
        $monedaSimbolo = request()->cookie('preferencia_moneda', 'EUR'); // Moneda
        $page = request()->query('page', 1);
        
        // MOCK de Muebles (Mismos datos que en CarritoController)
        $muebles = [
            'MESA1' => ['id' => 'MESA1', 'nombre' => 'Mesa de Comedor Lusso', 'precio' => 250.00, 'stock' => 5],
            'SOFA2' => ['id' => 'SOFA2', 'nombre' => 'Sofá Modular Confort', 'precio' => 850.00, 'stock' => 12],
            'SILLA3' => ['id' => 'SILLA3', 'nombre' => 'Silla Eames Clásica', 'precio' => 75.00, 'stock' => 0],
            // Añadir más mocks para probar paginación (> 12)
            'COF1' => ['id' => 'COF1', 'nombre' => 'Mesa de Centro', 'precio' => 150.00, 'stock' => 8],
            'LAMP1' => ['id' => 'LAMP1', 'nombre' => 'Lámpara de Pie', 'precio' => 95.00, 'stock' => 15],
            'CAMA1' => ['id' => 'CAMA1', 'nombre' => 'Cama Matrimonial', 'precio' => 1200.00, 'stock' => 3],
            'SOFA3' => ['id' => 'SOFA3', 'nombre' => 'Sofá Cama', 'precio' => 950.00, 'stock' => 6],
            'MESA2' => ['id' => 'MESA2', 'nombre' => 'Escritorio', 'precio' => 320.00, 'stock' => 4],
            'SILLA4' => ['id' => 'SILLA4', 'nombre' => 'Silla Eames Azul', 'precio' => 75.00, 'stock' => 2],
            'COF2' => ['id' => 'COF2', 'nombre' => 'Cofre', 'precio' => 150.00, 'stock' => 10],
            'LAMP2' => ['id' => 'LAMP2', 'nombre' => 'Lámpara de Mesa', 'precio' => 45.00, 'stock' => 20],
            'CAMA2' => ['id' => 'CAMA2', 'nombre' => 'Cama Individual', 'precio' => 600.00, 'stock' => 5],
            'MESA3' => ['id' => 'MESA3', 'nombre' => 'Mesa Auxiliar', 'precio' => 90.00, 'stock' => 7],
        ];

        // 2. Lógica de Paginación Manual (R1.c)
        $totalItems = count($muebles);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        $mueblesPaginated = array_slice($muebles, $offset, $itemsPerPage);

    @endphp

    <h2 class="mb-4">Catálogo de Muebles ({{ $totalItems }} ítems)</h2>
    
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        @foreach ($mueblesPaginated as $mueble)
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <img src="https://via.placeholder.com/200x150/{{ $mueble['id'] }}" class="card-img-top" alt="{{ $mueble['nombre'] }}"> 
                    
                    <div class="card-body text-center d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title">{{ $mueble['nombre'] }}</h5>
                            {{-- APLICACIÓN DE MONEDA DINÁMICA (R1.b) --}}
                            <p><strong>{{ number_format($mueble['precio'], 2) }} {{ $monedaSimbolo }}</strong></p>
                            <p class="text-muted">Stock: {{ $mueble['stock'] }}</p>
                        </div>

                        {{-- Formulario para añadir al carrito (Requerimiento 4.a) --}}
                        <form method="POST" action="{{ route('carrito.add', ['muebleId' => $mueble['id']]) }}">
                            @csrf
                            <div class="d-grid gap-2"> 
                                <input class="form-control text-center" 
                                       type="number" 
                                       name="cantidad" 
                                       value="1" 
                                       min="1" 
                                       max="{{ $mueble['stock'] }}" 
                                       style="width: 100px; margin: 0 auto;"
                                       @if($mueble['stock'] == 0) disabled @endif
                                       required>
                                
                                <button type="submit" class="btn btn-primary mt-2" 
                                        @if($mueble['stock'] == 0) disabled @endif>
                                    Agregar al carrito
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Controles de Paginación --}}
    @if ($totalPages > 1)
        <nav aria-label="Paginación">
            <ul class="pagination justify-content-center">
                {{-- Botón Anterior --}}
                <li class="page-item @if($page <= 1) disabled @endif">
                    <a class="page-link" href="?page={{ $page - 1 }}">Anterior</a>
                </li>
                
                {{-- Números de página --}}
                @for ($i = 1; $i <= $totalPages; $i++)
                    <li class="page-item @if($i == $page) active @endif">
                        <a class="page-link" href="?page={{ $i }}">{{ $i }}</a>
                    </li>
                @endfor

                {{-- Botón Siguiente --}}
                <li class="page-item @if($page >= $totalPages) disabled @endif">
                    <a class="page-link" href="?page={{ $page + 1 }}">Siguiente</a>
                </li>
            </ul>
        </nav>
    @endif
@endsection