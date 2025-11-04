@extends('cabecera')

@section('contenido')
    <h2 class="mb-4">Tu carrito</h2>

    @if (empty($carrito))
        <div class="alert alert-info">No tienes muebles en el carrito.</div>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($carrito as $id => $item)
                    <tr>
                        <td>{{ $item['nombre'] }}</td>
                        <td>{{ number_format($item['precio'], 2) }} €</td>
                        
                        {{-- Columna de Cantidad con Formulario de Actualizar (Requerimiento 4.a) --}}
                        <td>
                            <form method="POST" action="{{ route('carrito.update', ['muebleId' => $id]) }}" class="d-flex align-items-center">
                                @csrf
                                <input type="number" name="cantidad" value="{{ $item['cantidad'] }}" min="1" max="{{ $item['stock_disponible'] ?? 10 }}" class="form-control me-2" style="width: 80px;" required>
                                <button class="btn btn-sm btn-info" type="submit">Actualizar</button>
                            </form>
                        </td>
                        
                        <td>{{ number_format($item['precio'] * $item['cantidad'], 2) }} €</td>
                        
                        {{-- Botón Eliminar (Requerimiento 4.a) --}}
                        <td>
                            <form method="POST" action="{{ route('carrito.remove', ['muebleId' => $id]) }}">
                                @csrf
                                <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totales (Requerimiento 4.b) --}}
        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="totales-carrito">
                    <h4>Subtotal: {{ number_format($total / 1.16, 2) }} €</h4>
                    <h4>Impuestos (16% simulado): {{ number_format($total - ($total / 1.16), 2) }} €</h4>
                    <h4 class="mt-3">Total: {{ number_format($total, 2) }} €</h4>
                </div>
            </div>
        </div>
        

        <div class="mt-3 d-flex gap-2 justify-content-end">
            {{-- Botón Vaciar Carrito (Requerimiento 4.a) --}}
            <form method="POST" action="{{ route('carrito.clear') }}">
                @csrf
                <button class="btn btn-warning" type="submit" >Vaciar carrito</button>
            </form>
            <a href="{{ route('principal') }}" class="btn btn-secondary">Seguir comprando</a>
        </div>
    @endif
@endsection