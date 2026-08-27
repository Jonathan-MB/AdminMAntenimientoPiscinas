@include('partials.head')
<link rel="stylesheet" href="@recurso('css/panel.css')">
<title>Panel</title>

@include('partials.header')

<div class="contenedor-general">

    <div class="linea-titulo-panel">
        <h1 class="vista-titulo sin-borde">Últimas jornadas</h1>

        <a class="boton-primario" href="{{ route('registro.index') }}">Registrar jornada</a>
    </div>

    @include('partials.mensaje')

    @if (! empty($sinHotel))
        <div class="mensaje mensaje-alerta">
            Tu usuario todavía no tiene un hotel asignado. Pide a un administrador que te lo asigne.
        </div>
    @endif

    @forelse ($jornadas as $jornada)
        @php
            $hechas   = $jornada->rondas->sum('mediciones_count');
            $esperado = $jornada->hotel->piscinas_count * $jornada->hotel->rondas_programadas_count;
            $completa = $esperado > 0 && $hechas >= $esperado;
        @endphp

        <div class="tarjeta-jornada">

            <div class="jornada-fecha">
                <strong>{{ $jornada->fecha->format('d/m') }}</strong>
                <span>{{ $jornada->fecha->format('Y') }}</span>
            </div>

            <div class="jornada-datos">
                <span class="jornada-hotel">{{ $jornada->hotel->nombre }}</span>
                <span class="jornada-quien">Registró {{ $jornada->usuario->nombre_usuario }}</span>
            </div>

            <span class="jornada-avance {{ $completa ? 'avance-completo' : '' }}">
                {{ $hechas }} de {{ $esperado }}
            </span>

            <div class="jornada-acciones">
                <a class="boton-secundario boton-chico" href="{{ route('diario.index', ['hotel' => $jornada->hotel, 'fecha' => $jornada->fecha->format('Y-m-d')]) }}">Ver</a>
                <a class="boton-secundario boton-chico" href="{{ route('registro.jornada', $jornada) }}">Abrir</a>
            </div>

        </div>
    @empty
        @if (empty($sinHotel))
            <p class="sin-jornadas">Todavía no hay jornadas registradas.</p>
        @endif
    @endforelse

</div>

@include('partials.footer')
