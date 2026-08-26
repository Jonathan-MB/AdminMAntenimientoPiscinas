</head>

<body>

<header class="barra-superior">

    <a class="barra-superior-marca" href="{{ route('panel') }}">
        <img class="barra-superior-logo" src="{{ asset('img/isotipo-cuadrado.png') }}" alt="AQUALIVE">
        AQUALIVE
        <span class="barra-superior-descriptor">Pool Technology</span>
    </a>

    @auth
        <nav class="barra-superior-menu">
            <a class="enlace-menu" href="{{ route('panel') }}">Panel</a>

            @if (auth()->user()->tieneRol(\App\Models\Rol::MASTER, \App\Models\Rol::ADMINISTRADOR, \App\Models\Rol::COLABORADOR))
                <a class="enlace-menu" href="{{ route('registro.index') }}">Registro</a>
            @endif

            @if (auth()->user()->tieneRol(\App\Models\Rol::MASTER, \App\Models\Rol::ADMINISTRADOR))
                <a class="enlace-menu" href="{{ route('hoteles.index') }}">Hoteles</a>
                <a class="enlace-menu" href="{{ route('usuarios.index') }}">Usuarios</a>
            @endif
        </nav>

        <div class="barra-superior-sesion">
            <span class="sesion-usuario">{{ auth()->user()->nombre_usuario }}</span>

            <form method="POST" action="{{ route('acceso.cerrar') }}">
                @csrf
                <button class="boton-salir" type="submit">Salir</button>
            </form>
        </div>
    @endauth

</header>
