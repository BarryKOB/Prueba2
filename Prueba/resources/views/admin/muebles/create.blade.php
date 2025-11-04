@extends('cabecera')

@section('titulo', 'Nuevo mueble')

@section('contenido')
    <h2>Nueva mueble</h2>

    <form method="POST" action="{{ route('muebles.store') }}">
        @csrf
        <input type="text" name="nombre" placeholder="nombre" required>
        <input type="number" step="0.1" name="precio" placeholder="Precio (€)" required>
        <input type="number" name="stock" placeholder="stock" required>
        <input type="text" name="materiales" placeholder="materiales" required>
        <input type="text" name="dimensiones" placeholder="dimensiones" required>
        <input type="text" name="color_principal" placeholder="color principal" required>
        <input type="text" name="destacado" placeholder="destacado" required>


        {{-- Sección de Categorías (CORRECCIÓN) --}}
        <h3>Categorías</h3>
        <p>Selecciona una o más categorías:</p>
        
        <div id="categorias">
            {{-- NOTA: Este bucle debe iterar sobre las categorías pasadas por el MuebleController::create --}}
            @php $categorias = session('categorias', []); @endphp 
            @foreach ($categorias as $id => $cat)
                <div>
                    <input type="checkbox" name="categoria_id[]" value="{{ $cat['id'] }}" id="cat_{{ $cat['id'] }}">
                    <label for="cat_{{ $cat['id'] }}">{{ $cat['nombre'] }}</label>
                </div>
            @endforeach
        </div>

        {{-- Se elimina la función agregarCategoria() --}}
        <button class="btn-guardar" type="submit">Guardar mueble</button> 
    </form>
    <br>
    <a href="{{ url()->previous() }}" class="btn-volver">
        Volver
    </a>
@endsection