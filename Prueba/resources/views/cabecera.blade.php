<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Tienda de Muebles</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        /* Estilos CSS aquí, si los tienes */
        .totales-carrito { text-align: right; margin-top: 20px; }
        .totales-carrito h4 { font-weight: bold; }
        /* Lógica para Tema Obscuro (R1.a) */
        .theme-dark { background-color: #212529 !important; color: #f8f9fa !important; }
        .theme-dark .navbar { background-color: #343a40 !important; }
        .theme-dark .card { background-color: #495057 !important; color: #f8f9fa !important; }
    </style>
    @stack('styles')
</head>

<body class="bg-light @if(request()->cookie('preferencia_tema', 'claro') === 'obscuro') theme-dark @endif"> 
    @php
        // Lógica de sesión para la navegación
        $sesionActiva = Session::has('autorizacion_usuario') && Session::get('autorizacion_usuario');
        $usuarioData = $sesionActiva && Session::has('usuario') ? json_decode(Session::get('usuario')) : null;
    @endphp

    {{-- BARRA DE NAVEGACIÓN UNIFICADA --}}
    <nav class="navbar navbar-expand-lg @if(request()->cookie('preferencia_tema', 'claro') === 'obscuro') navbar-dark bg-dark @else navbar-light bg-light @endif">
        <div class="container-fluid">
            <a href="{{ route('principal') }}" class="navbar-brand">🏠 Tienda de Muebles</a>
            
            <div class="ms-auto d-flex align-items-center gap-3">
                
                @if ($usuarioData)
                    <span class="navbar-text @if(request()->cookie('preferencia_tema', 'claro') === 'obscuro') text-white-50 @else text-muted @endif">
                        Usuario Activo: {{ $usuarioData->nombre }} 
                    </span>
                @endif
                
                <a href="{{ route('carrito.show') }}" class="btn btn-outline-light">Ver Carrito</a>
                
                @if ($sesionActiva)
                    @if ($usuarioData && $usuarioData->rol == \App\Enums\RolUser::ADMIN)
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-info">Panel de Administración</a>
                    @endif
                    
                    <a href="{{ route('preferencias.edit') }}" class="btn btn-outline-secondary">⚙️ Prefs</a> 

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

        @yield('contenido')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>