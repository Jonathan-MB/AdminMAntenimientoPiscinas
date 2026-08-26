@include('partials.head')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
<title>Ingresar</title>

@include('partials.header-limpio')

<div class="contenedor-acceso">

    <div class="tarjeta-acceso">

        <img class="acceso-logo" src="{{ asset('img/logo-400.png') }}" alt="AQUALIVE Pool Technology">

        <h1 class="acceso-titulo">Control de mantenimiento</h1>

        @include('partials.mensaje')

        @if ($errors->any())
            <div class="mensaje mensaje-error">{{ $errors->first() }}</div>
        @endif

        <form class="formulario-acceso" method="POST" action="{{ route('acceso.iniciar') }}">
            @csrf

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="nombreUsuario">Nombre de usuario</label>
                <input class="campo-formulario" type="text" id="nombreUsuario" name="nombreUsuario"
                       value="{{ old('nombreUsuario') }}" maxlength="45" autocomplete="username" autofocus required>
            </div>

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="password">Contraseña</label>
                <input class="campo-formulario" type="password" id="password" name="password"
                       autocomplete="current-password" required>
            </div>

            <div class="linea-recordarme">
                <input type="checkbox" id="recordarme" name="recordarme" value="1">
                <label for="recordarme">Mantener la sesión abierta</label>
            </div>

            <button class="boton-primario boton-ancho" type="submit">Ingresar</button>
        </form>

    </div>

    <p class="acceso-pie">AQUALIVE · Pool Technology</p>

</div>

@include('partials.footer')
