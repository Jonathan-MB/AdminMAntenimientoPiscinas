@include('partials.head')
<link rel="stylesheet" href="@recurso('css/registro.css')">
<title>Abrir jornada</title>

@include('partials.header')

<div class="contenedor-general">

    <div class="contenedor-estrecho">

        <h1 class="vista-titulo titulo-centrado">Registro del día</h1>

        @include('partials.mensaje')

        @if ($errors->any())
            <div class="mensaje mensaje-error">{{ $errors->first() }}</div>
        @endif

        <div class="tarjeta-abrir">
            <h2 class="titulo-seccion titulo-centrado">Abrir o retomar una jornada</h2>

            <form method="POST" action="{{ route('registro.store') }}">
                @csrf

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="hotelId">Hotel</label>
                    <select class="campo-formulario campo-grande" id="hotelId" name="hotelId" required>
                        <option value="">Elige el hotel</option>
                        @foreach ($hoteles as $hotel)
                            <option value="{{ $hotel->id }}" @selected(old('hotelId') == $hotel->id)>{{ $hotel->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                @if (auth()->user()->tieneRol(\App\Models\Rol::MASTER, \App\Models\Rol::ADMINISTRADOR))
                    <div class="elemento-formulario">
                        <label class="titulo-elemento" for="fecha">Fecha</label>
                        <input class="campo-formulario campo-grande" type="date" id="fecha" name="fecha"
                               value="{{ old('fecha', now()->toDateString()) }}"
                               max="{{ now()->toDateString() }}" required>
                        <p class="nota-formulario">Solo tú puedes abrir un día pasado, para corregirlo.</p>
                    </div>
                @else
                    <div class="elemento-formulario">
                        <span class="titulo-elemento">Fecha</span>
                        <p class="fecha-fija">{{ $hoyLargo }}</p>
                    </div>
                @endif

                <p class="nota-formulario">La fecha y la hora van en el horario de Aruba.</p>

                <button class="boton-primario boton-ancho boton-grande" type="submit">Continuar</button>
            </form>
        </div>

    </div>

    @if ($recientes->count())
        <h2 class="titulo-seccion titulo-recientes">Jornadas recientes</h2>

        <div class="encabezado-historial">
            <span class="fila-fecha">Fecha</span>
            <span class="fila-datos">Hotel y quién registró</span>
        </div>

        @foreach ($recientes as $reciente)
            <a class="fila-historial" href="{{ route('registro.jornada', $reciente) }}">

                <div class="fila-fecha">
                    <strong>{{ $reciente->fecha->format('d/m') }}</strong>
                    <span>{{ $reciente->fecha->format('Y') }}</span>
                </div>

                <div class="fila-datos">
                    <span class="fila-titulo">{{ $reciente->hotel->nombre }}</span>
                    <span class="fila-nota">Registró {{ $reciente->usuario->nombre_usuario }}</span>
                </div>

                @if ($reciente->esDeHoy())
                    <span class="fila-marca marca-azul">Hoy</span>
                @endif

            </a>
        @endforeach
    @endif

</div>

@include('partials.footer')
