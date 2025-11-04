<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Administración</title>
</head>

<body>
    @if (Session::has('autorizacion_usuario'))
        <h1>Bienvenido, {{ $usuario->nombre }} </h1>
        <p>Email: {{ $usuario->email }}</p>

        <h1> Panel de administración. </h1>
        <nav>
            <a href="{{ route('categorias.index') }}">Administración de Categorias</a>
            <a href="{{ route('muebles.index') }}">Administración de Muebles</a>

        </nav>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Cerrar sesión</button>
        </form>
    @else
        <p>Debes iniciar sesión.</p>
    @endif
</body>

</html>
