<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Tienda de Muebles</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        /* Estilo simple para centrar los totales como en tu ejemplo */
        .totales-carrito {
            text-align: right;
            margin-top: 20px;
        }
        .totales-carrito h4 {
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-light">
    {{-- Barra de Navegación Mínima para no causar errores --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a href="{{ route('principal') }}" class="navbar-brand">Tienda de Muebles</a>
            <div class="d-flex gap-2">
                <a href="{{ route('carrito.show') }}" class="btn btn-outline-light">Ver Carrito</a>
                {{-- No incluimos la lógica de sesión del usuario ya que no es el foco --}}
            </div>
        </div>
    </nav>

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
</body>
</html>