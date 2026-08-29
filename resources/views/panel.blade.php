@include('partials.head')
<link rel="stylesheet" href="@recurso('css/panel.css')">
<title>Panel</title>

@include('partials.header')

<div class="contenedor-general">

    <div class="linea-titulo">
        <h1 class="vista-titulo sin-borde">Jornadas</h1>

        <a class="boton-primario" href="{{ route('registro.index') }}">Registrar jornada</a>
    </div>

    @include('partials.mensaje')

    @if (! empty($sinHotel))
        <div class="mensaje mensaje-alerta">
            Tu usuario todavía no tiene un hotel asignado. Pide a un administrador que te lo asigne.
        </div>
    @else

        {{-- --------------------FILTROS------------------- --}}

        <form class="barra-filtros" method="GET" action="{{ route('panel') }}">

            <div class="elemento-filtro">
                <label class="titulo-elemento" for="hotel">Hotel</label>
                <select class="campo-formulario" id="hotel" name="hotel">
                    <option value="">Todos</option>
                    @foreach ($hoteles as $hotel)
                        <option value="{{ $hotel->id }}" @selected($hotelId == $hotel->id)>{{ $hotel->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="elemento-filtro">
                <label class="titulo-elemento" for="empleado">Empleado</label>
                <select class="campo-formulario" id="empleado" name="empleado">
                    <option value="">Todos</option>
                    @foreach ($empleados as $empleado)
                        <option value="{{ $empleado->id }}" @selected($empleadoId == $empleado->id)>{{ $empleado->nombre_usuario }}</option>
                    @endforeach
                </select>
            </div>

            <div class="elemento-filtro elemento-fecha">
                <label class="titulo-elemento" for="desde">Desde</label>
                <input class="campo-formulario" type="date" id="desde" name="desde" value="{{ $desde }}">
            </div>

            <div class="elemento-filtro elemento-fecha">
                <label class="titulo-elemento" for="hasta">Hasta</label>
                <input class="campo-formulario" type="date" id="hasta" name="hasta" value="{{ $hasta }}">
            </div>

            <div class="botones-filtro">
                <button class="boton-primario" type="submit">Filtrar</button>

                @if ($hotelId || $empleadoId || $desde || $hasta)
                    <a class="boton-secundario" href="{{ route('panel') }}">Limpiar</a>
                @endif
            </div>

        </form>

        <p class="conteo-resultados">
            @if ($total === 0)
                Ninguna jornada coincide con el filtro.
            @else
                {{ $total }} {{ $total === 1 ? 'jornada' : 'jornadas' }}.

                @if ($jornadas->hasPages() && $jornadas->firstItem())
                    Viendo de la {{ $jornadas->firstItem() }} a la {{ $jornadas->lastItem() }},
                    página {{ $jornadas->currentPage() }} de {{ $jornadas->lastPage() }}.
                @endif
            @endif
        </p>

        @if ($jornadas->count())
            <div class="encabezado-historial">
                <span class="fila-fecha">Fecha</span>
                <span class="fila-datos">Hotel y quién registró</span>
                <span class="fila-marca">Mediciones</span>
                <span class="fila-acciones">Acciones</span>
            </div>
        @endif

        @foreach ($jornadas as $jornada)
            @php
                $hechas   = $jornada->rondas->sum('mediciones_count');
                $esperado = $jornada->hotel->piscinas_count * $jornada->hotel->rondas_programadas_count;
                $completa = $esperado > 0 && $hechas >= $esperado;
            @endphp

            <div class="fila-historial {{ $jornada->cambios_count > 0 ? 'fila-corregida' : '' }}">

                <div class="fila-fecha">
                    <strong>{{ $jornada->fecha->format('d/m') }}</strong>
                    <span>{{ $jornada->fecha->format('Y') }}</span>
                </div>

                <div class="fila-datos">
                    <span class="fila-titulo">{{ $jornada->hotel->nombre }}</span>
                    <span class="fila-nota">Registró {{ $jornada->usuario->nombre_usuario }}</span>
                </div>

                @if ($jornada->cambios_count > 0)
                    <a class="fila-marca marca-corregida"
                       href="{{ route('cambios.index', $jornada) }}"
                       title="Se corrigieron valores después de guardarlos. Toca para ver qué cambió.">
                        {{ $jornada->cambios_count }} {{ $jornada->cambios_count === 1 ? 'corrección' : 'correcciones' }}
                    </a>
                @endif

                <span class="fila-marca {{ $completa ? 'marca-verde' : '' }}"
                      title="{{ $jornada->hotel->piscinas_count }} piscinas × {{ $jornada->hotel->rondas_programadas_count }} rondas">
                    {{ $hechas }} de {{ $esperado }}
                </span>

                <div class="fila-acciones">
                    <a class="boton-secundario boton-chico"
                       href="{{ route('diario.index', ['hotel' => $jornada->hotel, 'fecha' => $jornada->fecha->format('Y-m-d')]) }}"
                       title="Consultar este día en el diario del hotel">Diario</a>

                    <a class="boton-secundario boton-chico"
                       href="{{ route('registro.jornada', $jornada) }}"
                       title="Abrir el registro para completarlo o corregirlo">Editar</a>
                </div>

            </div>
        @endforeach

        @include('partials.paginacion', ['paginador' => $jornadas])

        @if ($total === 0)
            <p class="caja-vacia">
                @if ($hotelId || $empleadoId || $desde || $hasta)
                    Prueba quitando alguno de los filtros.
                @else
                    Todavía no hay jornadas registradas.
                @endif
            </p>
        @elseif ($jornadas->isEmpty())
            {{-- Se pidio una pagina mas alla de la ultima: pasa al volver atras
                 despues de acotar el filtro --}}
            <p class="caja-vacia">
                Esa página ya no tiene resultados.
                <a href="{{ $jornadas->url(1) }}">Volver a la primera</a>.
            </p>
        @endif

    @endif

</div>

@include('partials.footer')
