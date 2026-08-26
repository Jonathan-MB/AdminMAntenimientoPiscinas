@include('partials.head')
<link rel="stylesheet" href="{{ asset('css/registro.css') }}">
<title>Abrir jornada</title>

@include('partials.header')

<div class="contenedor-general">
    <h1 class="vista-titulo">Registro del día</h1>

    @include('partials.mensaje')

    @if ($errors->any())
        <div class="mensaje mensaje-error">{{ $errors->first() }}</div>
    @endif

    <div class="tarjeta-abrir">
        <h2 class="titulo-seccion">Abrir o retomar una jornada</h2>

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

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="fecha">Fecha</label>
                <input class="campo-formulario campo-grande" type="date" id="fecha" name="fecha"
                       value="{{ old('fecha', now()->toDateString()) }}"
                       max="{{ now()->toDateString() }}" required>
            </div>

            <p class="nota-formulario">La fecha y la hora van en el horario de Aruba.</p>

            <button class="boton-primario boton-ancho boton-grande" type="submit">Continuar</button>
        </form>
    </div>

    @if ($recientes->count())
        <h2 class="titulo-seccion titulo-recientes">Jornadas recientes</h2>

        <div class="lista-recientes">
            @foreach ($recientes as $reciente)
                <a class="tarjeta-reciente" href="{{ route('registro.jornada', $reciente) }}">
                    <span class="fecha-reciente">{{ $reciente->fecha->format('d/m/Y') }}</span>
                    <span class="hotel-reciente">{{ $reciente->hotel->nombre }}</span>

                    @if ($reciente->esDeHoy())
                        <span class="marca-hoy">Hoy</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

</div>

@include('partials.footer')
