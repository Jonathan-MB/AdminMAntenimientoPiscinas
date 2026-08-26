@include('partials.head')
<link rel="stylesheet" href="{{ asset('css/panel.css') }}">
<title>Panel</title>

@include('partials.header')

<div class="contenedor-general">
    <h1 class="vista-titulo">Panel</h1>

    @include('partials.mensaje')

    <div class="tarjeta-sesion">
        <p class="dato-sesion"><span class="titulo-elemento">Usuario</span> {{ $usuario->nombre_usuario }}</p>
        <p class="dato-sesion"><span class="titulo-elemento">Correo</span> {{ $usuario->correo }}</p>
        <p class="dato-sesion"><span class="titulo-elemento">Rol</span> {{ $usuario->rol->nombre }}</p>
    </div>

    @if ($usuario->tieneRol(\App\Models\Rol::MASTER, \App\Models\Rol::ADMINISTRADOR))
        <a class="boton-primario" href="{{ route('usuarios.index') }}">Administrar usuarios</a>
    @endif

    @if ($usuario->tieneRol(\App\Models\Rol::HOTEL) && $usuario->hotel_id)
        <a class="boton-primario" href="{{ route('diario.index', $usuario->hotel_id) }}">Ver el diario de mis piscinas</a>
    @endif

    @if ($usuario->tieneRol(\App\Models\Rol::COLABORADOR))
        <p class="texto-vacio">Aquí registrarás los mantenimientos cuando el módulo esté listo.</p>
    @endif
</div>

@include('partials.footer')
