<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>@yield('titulo', 'Tienda de muebles')</title>
    {{-- Asegúrate de que este enlace sea el ÚNICO para Bootstrap --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        /* Estilos CSS aquí, si los tienes */
        .totales-carrito { text-align: right; margin-top: 20px; }
        .totales-carrito h4 { font-weight: bold; }
        /* Puedes añadir aquí los estilos para el tema oscuro si los implementaste */
    </style>
</head>

<body class="bg-light">
    @php
        $sesionActiva = Session::has('autorizacion_usuario') && Session::get('autorizacion_usuario');
        $usuarioData = $sesionActiva && Session::has('usuario') ? json_decode(Session::get('usuario')) : null;
    @endphp

    {{-- BARRA DE NAVEGACIÓN UNIFICADA --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a href="{{ route('principal') }}" class="navbar-brand">🏠 Tienda de Muebles</a>
            
            <div class="ms-auto d-flex align-items-center gap-3">
                
                @if ($usuarioData)
                    <span class="navbar-text text-white-50">
                        Usuario Activo: {{ $usuarioData->nombre }} 
                    </span>
                    
                    {{-- Comprobación de Rol (R5) --}}
                    @if (isset($usuarioData->rol) && $usuarioData->rol === \App\Enums\RolUser::ADMIN->value)
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-info">Panel de Administración</a>
                        <a href="{{ route('preferencias.edit') }}" class="btn btn-outline-secondary">⚙️ Preferencias</a>
                    @endif
                    {{-- Comprobación de Rol (R5) --}}
                    @if (isset($usuarioData->rol) && $usuarioData->rol === \App\Enums\RolUser::USUARIO->value)
                        <a href="{{ route('carrito.show') }}" class="btn btn-outline-light">Ver Carrito</a>
                        <a href="{{ route('preferencias.edit') }}" class="btn btn-outline-secondary">⚙️ Preferencias</a>
                    @endif
                @endif
            
    
                
                @if ($sesionActiva)
                    <form action="{{ route('logout') }}" method="POST" class="d-flex"> 
                        @csrf
                        <button class="btn btn-outline-danger" type="submit">Cerrar Sesión</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-success">Iniciar Sesión</a>
                @endif
            </div>
        </div>
    </nav>
    {{-- FIN BARRA DE NAVEGACIÓN --}}

    <div class="container mt-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        {{-- ZONA DE CONTENIDO DINÁMICO --}}
        <main>
            @yield('contenido')
        </main>
        {{-- FIN ZONA DE CONTENIDO DINÁMICO --}}

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>