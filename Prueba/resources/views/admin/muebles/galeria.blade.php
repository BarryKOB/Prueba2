@extends('cabecera')

@section('titulo', 'Galería: ' . $mueble->getNombre())

@section('contenido')
    <h2>{{ $mueble->getNombre() }}</h2>

    @php
        $categoriasIds = $mueble->getCategoria() ?? [];
        $categorias = session('categorias', []);
        $categoriasNombres = [];
        foreach ($categorias as $cat) {
            if (in_array($cat['id'], $categoriasIds)) {
                $categoriasNombres[] = $cat['nombre'];
            }
        }
    @endphp

    <div class="mueble-info">
        <p><strong>Descripción:</strong> {{ $mueble->getDescripcion() ?? '-' }}</p>
        <p><strong>Precio:</strong> {{ $mueble->getPrecio() }} €</p>
        <p><strong>Stock:</strong> {{ $mueble->getStock() }}</p>
        <p><strong>Materiales:</strong> {{ $mueble->getMateriales() ?? '-' }}</p>
        <p><strong>Dimensiones:</strong> {{ $mueble->getDimensiones() ?? '-' }}</p>
        <p><strong>Color principal:</strong> {{ $mueble->getColorPrincipal() ?? '-' }}</p>
        <p><strong>Destacado:</strong> {{ $mueble->getDestacado() ? 'Sí' : 'No' }}</p>
        <p><strong>Categorías:</strong> {{ empty($categoriasNombres) ? 'Sin categoría' : implode(', ', $categoriasNombres) }}</p>
    </div>

    <hr>

    <h3>Subir imágenes</h3>
    <form action="{{ route('mueble.gallery.upload', $mueble->getId()) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="imagen[]" accept="image/*" multiple required>
        <button type="submit">Subir</button>
    </form>

    <hr>

    <h3>Imágenes</h3>
    @if ($mueble->getImagenes())
        <div class="imagenes" style="display:flex; flex-wrap: wrap; gap: 12px;">
            @foreach ($mueble->getImagenes() as $imagen)
                @php $nombre = basename($imagen); @endphp
                <div class="imagen-item" style="border:1px solid #ddd; padding:8px;">
                    <div>
                        <img src="{{ route('mueble.imagen', ['id' => $mueble->getId(), 'imagen' => $nombre]) }}"
                             alt="{{ $nombre }}" style="max-width: 200px; height: auto;">
                    </div>
                    <form method="POST" action="{{ route('mueble.gallery.delete', ['id' => $mueble->getId(), 'imagen' => $nombre]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('¿Eliminar esta imagen?')">Eliminar</button>
                    </form>
                </div>
            @endforeach
        </div>
    @else
        <p>No hay imágenes.</p>
    @endif

    <br>
    <a href="{{ route('muebles.index') }}" class="btn-volver">Volver</a>
@endsection