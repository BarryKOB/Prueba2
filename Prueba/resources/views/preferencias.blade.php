@extends('cabecera')

@section('contenido')
<div class="container mt-4">
    <h2>Preferencias de Usuario</h2>

    @if(session('mensaje'))
        <div class="alert alert-success">
            {{ session('mensaje') }}
        </div>
    @endif

    <form action="{{ route('preferencias.update') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="moneda" class="form-label">Moneda</label>
            <select name="moneda" id="moneda" class="form-select">
                <option value="EUR" {{ $moneda == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                <option value="USD" {{ $moneda == 'USD' ? 'selected' : '' }}>USD ($)</option>
                <option value="GBP" {{ $moneda == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="paginacion" class="form-label">Elementos por página</label>
            <select name="paginacion" id="paginacion" class="form-select">
                <option value="6" {{ $paginacion == '6' ? 'selected' : '' }}>6 elementos</option>
                <option value="12" {{ $paginacion == '12' ? 'selected' : '' }}>12 elementos</option>
                <option value="24" {{ $paginacion == '24' ? 'selected' : '' }}>24 elementos</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Preferencias</button>
    </form>
</div>
@endsection