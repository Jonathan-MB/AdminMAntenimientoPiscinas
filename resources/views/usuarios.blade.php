@include('partials.head')
<link rel="stylesheet" href="@recurso('css/usuarios.css')">
<title>Usuarios</title>

@include('partials.header')

<div class="contenedor-general">
    <div class="linea-titulo">
        <h1 class="vista-titulo sin-borde">Usuarios</h1>

        <button class="boton-primario" type="button" id="botonAbrirCrear">Crear usuario</button>
    </div>

    @include('partials.mensaje')

    @if ($errors->any())
        <div class="mensaje mensaje-error">{{ $errors->first() }}</div>
    @endif

    <div class="caja-tabla">
        <table class="tabla-usuarios">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Hotel</th>
                    <th>Estado</th>
                    <th class="columna-acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    <tr data-usuario="{{ $usuario->id }}">
                        <td data-titulo="Usuario">{{ $usuario->nombre_usuario }}</td>
                        <td data-titulo="Correo">{{ $usuario->correo }}</td>
                        <td data-titulo="Rol"><span class="etiqueta-rol etiqueta-{{ $usuario->rol->nombre }}">{{ $usuario->rol->nombre }}</span></td>
                        <td data-titulo="Hotel">{{ $usuario->hotel?->nombre ?: '—' }}</td>
                        <td data-titulo="Estado">{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</td>
                        <td data-titulo="Acciones" class="columna-acciones">
                            @if (auth()->user()->esMaster() && $usuario->id !== auth()->id() && $usuario->activo)
                                <form class="formulario-ver-como" method="POST" action="{{ route('suplantacion.iniciar', $usuario) }}">
                                    @csrf
                                    <button class="boton-secundario boton-chico" type="submit">Ver como</button>
                                </form>
                            @endif

                            @if ($usuario->rol->nombre === \App\Models\Rol::MASTER)
                                <span class="nota-bloqueado">No se puede eliminar</span>
                            @elseif ($usuario->id === auth()->id())
                                <span class="nota-bloqueado">Eres tú</span>
                            @elseif ($usuario->rol->nombre === \App\Models\Rol::ADMINISTRADOR && ! auth()->user()->esMaster())
                                <span class="nota-bloqueado">Solo el master</span>
                            @else
                                <button class="boton-eliminar" type="button"
                                        data-id="{{ $usuario->id }}"
                                        data-nombre="{{ $usuario->nombre_usuario }}">Eliminar</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- --------------------POP UP CREAR------------------- --}}

    <div class="fondo-popup" id="fondoPopupCrear">
        <div class="popup">

            <h2 class="titulo-popup">Crear usuario</h2>

            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="nombreUsuario">Nombre de usuario</label>
                    <input class="campo-formulario" type="text" id="nombreUsuario" name="nombreUsuario"
                           value="{{ old('nombreUsuario') }}" maxlength="45" required>
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="correo">Correo</label>
                    <input class="campo-formulario" type="email" id="correo" name="correo"
                           value="{{ old('correo') }}" maxlength="120" required>
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="password">Contraseña</label>
                    <input class="campo-formulario" type="password" id="password" name="password"
                           minlength="8" maxlength="60" required>
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="rolId">Rol</label>
                    <select class="campo-formulario" id="rolId" name="rolId" required>
                        <option value="">Elige un rol</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}" @selected(old('rolId') == $rol->id)>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="elemento-formulario" id="grupoHotel">
                    <label class="titulo-elemento" for="hotelId">Hotel que podrá consultar</label>
                    <select class="campo-formulario" id="hotelId" name="hotelId">
                        <option value="">Elige un hotel</option>
                        @foreach ($hoteles as $hotel)
                            <option value="{{ $hotel->id }}" @selected(old('hotelId') == $hotel->id)>{{ $hotel->nombre }}</option>
                        @endforeach
                    </select>
                    <p class="nota-formulario">Solo aplica al rol hotel.</p>
                </div>

                <div class="linea-botones-popup">
                    <button class="boton-secundario" type="button" id="botonCerrarCrear">Cancelar</button>
                    <button class="boton-primario" type="submit">Guardar</button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    const rutaUsuarios = '/usuarios';
    const rolHotelId = {{ $roles->firstWhere('nombre', \App\Models\Rol::HOTEL)?->id ?? 0 }};
</script>

<script src="@recurso('js/usuarios.js')"></script>

@include('partials.footer')
