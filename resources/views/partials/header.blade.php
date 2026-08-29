</head>

<body>

@if (session()->has(\App\Http\Controllers\SuplantacionController::LLAVE))
    <div class="franja-suplantacion">
        <span>
            Estás viendo la aplicación como
            <strong>{{ auth()->user()->nombre_usuario }}</strong>
            ({{ implode(', ', auth()->user()->etiquetasDeRoles()) }})
        </span>

        <form method="POST" action="{{ route('suplantacion.terminar') }}">
            @csrf
            <button class="boton-volver-cuenta" type="submit">Volver a mi cuenta</button>
        </form>
    </div>
@endif

<header class="barra-superior">

    <a class="barra-superior-marca" href="{{ route('panel') }}">
        <img class="barra-superior-logo" src="@recurso('img/isotipo-cuadrado.png')" alt="AQUALIVE">
        AQUALIVE
        <span class="barra-superior-descriptor">Pool Technology</span>
    </a>

    @auth
        <nav class="barra-superior-menu">
            <a class="enlace-menu" href="{{ route('panel') }}">Panel</a>

            @if (auth()->user()->tieneRol(\App\Models\Rol::MASTER, \App\Models\Rol::JEFE, \App\Models\Rol::REPARACION))
                <a class="enlace-menu enlace-destacado" href="{{ route('reparaciones.index') }}">
                    Reparaciones
                    <span class="marca-abiertos @if (! $reparacionesAbiertas) marca-vacia @endif"
                          id="marcaAbiertos"
                          title="Reparaciones sin cobrar">{{ $reparacionesAbiertas }}</span>
                </a>
            @endif

            @if (auth()->user()->tieneRol(\App\Models\Rol::MASTER, \App\Models\Rol::ADMINISTRADOR, \App\Models\Rol::COLABORADOR))
                <a class="enlace-menu" href="{{ route('registro.index') }}">Registro</a>
            @endif

            @if (auth()->user()->tieneRol(\App\Models\Rol::MASTER, \App\Models\Rol::ADMINISTRADOR))
                <a class="enlace-menu" href="{{ route('hoteles.index') }}">Hoteles</a>
                <a class="enlace-menu" href="{{ route('usuarios.index') }}">Usuarios</a>
            @endif
        </nav>

        <div class="barra-superior-sesion">
            <a class="sesion-usuario" href="{{ route('perfil.index') }}" title="Ver mi perfil">
                {{ auth()->user()->nombre_usuario }}
            </a>

            <form method="POST" action="{{ route('acceso.cerrar') }}">
                @csrf
                <button class="boton-salir" type="submit">Salir</button>
            </form>
        </div>
    @endauth

</header>
