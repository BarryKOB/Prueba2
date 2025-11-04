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


    <h3>Ingredientes</h3>
        <div id="categorias">
            <div>
                <input type="text" name="categorias[0][nombre]" placeholder="Nombre" required>
                <input type="text" name="categorias[0][descripcion]" placeholder="Descripción">
            </div>
        </div>

        <button class="btn-agregar" type="button" onclick="agregarCategoria()">+ Añadir categoria</button>
        <button class="btn-guardar" type="submit">Guardar receta</button>
    </form>
    <br>
    <a href="{{ url()->previous() }}" class="btn-volver">
        Volver
    </a>
@endsection
