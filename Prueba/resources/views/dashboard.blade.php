@extends('cabecera')

@section('titulo', 'Panel de Administración')

@section('contenido')
    <h2 class="mb-4">Panel de Administración</h2>
    
    @if (Session::has('autorizacion_usuario'))
        @php
             // Asegura que se pasa el usuario para mostrar el mensaje
             $usuario = json_decode(Session::get('usuario')); 
        @endphp
        
        <h1>Bienvenido, {{ $usuario->nombre ?? 'Administrador' }} </h1>
        <p>Email: {{ $usuario->email ?? 'N/A' }}</p>

        <hr>
        
        <h3 class="mt-4">Gestión de Contenido</h3>
        <nav class="d-flex flex-column gap-2">
            <a href="{{ route('categorias.index') }}">Administración de Categorias</a>
            <a href="{{ route('muebles.index') }}">Administración de Muebles</a>
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="btn btn-danger">Cerrar sesión</button>
        </form>
    @else
        <p class="alert alert-warning">Debes iniciar sesión.</p>
    @endif
@endsection