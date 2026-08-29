@include('partials.head')
<link rel="stylesheet" href="@recurso('css/perfil.css')">
<title>Mi perfil</title>

@include('partials.header')

<div class="contenedor-general">

  <div class="contenedor-medio">

    <h1 class="vista-titulo titulo-centrado">Mi perfil</h1>

    @include('partials.mensaje')

    @if ($errors->any())
        <div class="mensaje mensaje-error">{{ $errors->first() }}</div>
    @endif

    <div class="reja-perfil">

        {{-- --------------------DATOS------------------- --}}

        <div class="tarjeta-perfil">
            <h2 class="titulo-seccion">Mis datos</h2>

            <div class="dato-fijo">
                <span class="titulo-elemento">Usuario</span>
                <strong>{{ $usuario->nombre_usuario }}</strong>
            </div>

            <div class="dato-fijo">
                <span class="titulo-elemento">{{ count($usuario->nombresDeRoles()) === 1 ? 'Rol' : 'Roles' }}</span>
                <strong>{{ implode(', ', $usuario->etiquetasDeRoles()) }}</strong>
            </div>

            @if ($usuario->hotel)
                <div class="dato-fijo">
                    <span class="titulo-elemento">Hotel</span>
                    <strong>{{ $usuario->hotel->nombre }}</strong>
                </div>
            @endif

            <p class="nota-formulario">El nombre de usuario y los roles los cambia un administrador.</p>

            <form method="POST" action="{{ route('perfil.update') }}">
                @csrf
                @method('PATCH')

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="correo">Correo</label>
                    <input class="campo-formulario" type="email" id="correo" name="correo"
                           value="{{ old('correo', $usuario->correo) }}" maxlength="120" required>
                </div>

                <button class="boton-secundario" type="submit">Guardar correo</button>
            </form>
        </div>

        {{-- --------------------CONTRASENA------------------- --}}

        <div class="tarjeta-perfil">
            <h2 class="titulo-seccion">Cambiar contraseña</h2>

            <form method="POST" action="{{ route('perfil.password') }}">
                @csrf
                @method('PATCH')

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="passwordActual">Contraseña actual</label>
                    <input class="campo-formulario" type="password" id="passwordActual" name="passwordActual"
                           autocomplete="current-password" required>
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="password">Contraseña nueva</label>
                    <input class="campo-formulario" type="password" id="password" name="password"
                           minlength="8" maxlength="60" autocomplete="new-password" required>
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="passwordConfirmacion">Repite la contraseña nueva</label>
                    <input class="campo-formulario" type="password" id="passwordConfirmacion" name="passwordConfirmacion"
                           minlength="8" maxlength="60" autocomplete="new-password" required>
                </div>

                <p class="nota-formulario">Mínimo 8 caracteres, y distinta de la actual.</p>

                <button class="boton-primario" type="submit">Cambiar contraseña</button>
            </form>
        </div>

    </div>

  </div>

</div>

@include('partials.footer')
