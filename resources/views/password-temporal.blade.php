@include('partials.head')
<link rel="stylesheet" href="@recurso('css/login.css')">
<link rel="stylesheet" href="@recurso('css/password-temporal.css')">
<title>Elige tu contraseña</title>

@include('partials.header-limpio')

<div class="contenedor-acceso">

    <div class="tarjeta-acceso tarjeta-ancha">

        <img class="acceso-logo" src="@recurso('img/logo-400.png')" alt="AQUALIVE Pool Technology">

        <h1 class="acceso-titulo">Elige tu contraseña</h1>

        <p class="aviso-temporal">
            La contraseña con la que acabas de entrar <strong>te la puso un administrador</strong>,
            así que alguien más la conoce. Elige una tuya para seguir.
        </p>

        @if ($errors->any())
            <div class="mensaje mensaje-error">{{ $errors->first() }}</div>
        @endif

        <form class="formulario-acceso" method="POST" action="{{ route('password.temporal.update') }}">
            @csrf
            @method('PATCH')

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="password">Contraseña nueva</label>
                <input class="campo-formulario" type="password" id="password" name="password"
                       minlength="8" maxlength="60" autocomplete="new-password" required autofocus>
                <p class="nota-formulario">Mínimo 8 caracteres.</p>
            </div>

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="passwordConfirmacion">Repite la contraseña</label>
                <input class="campo-formulario" type="password" id="passwordConfirmacion" name="passwordConfirmacion"
                       minlength="8" maxlength="60" autocomplete="new-password" required>
            </div>

            <button class="boton-primario boton-ancho" type="submit">Guardar y continuar</button>
        </form>

        <form class="formulario-salir-temporal" method="POST" action="{{ route('acceso.cerrar') }}">
            @csrf
            <button class="enlace-salir-temporal" type="submit">Salir sin cambiarla</button>
        </form>

    </div>

</div>

@include('partials.footer')
