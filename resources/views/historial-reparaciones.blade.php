@include('partials.head')
<link rel="stylesheet" href="@recurso('css/reparaciones.css')">
<title>Historial de reparaciones</title>

@include('partials.header')

<div class="contenedor-general">

    <div class="linea-titulo">
        <h1 class="vista-titulo sin-borde">Reparaciones cobradas</h1>

        <a class="boton-secundario" href="{{ route('reparaciones.index') }}">← Volver al tablero</a>
    </div>

    @include('partials.mensaje')

    <form class="barra-filtros" method="GET" action="{{ route('reparaciones.historial') }}">

        <div class="elemento-filtro">
            <label class="titulo-elemento" for="hotel">Hotel</label>
            <select class="campo-formulario" id="hotel" name="hotel">
                <option value="">Todos</option>
                @foreach ($hoteles as $hotel)
                    <option value="{{ $hotel->id }}" @selected($hotelId == $hotel->id)>{{ $hotel->nombre }}</option>
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

            @if ($hotelId || $desde || $hasta)
                <a class="boton-secundario" href="{{ route('reparaciones.historial') }}">Limpiar</a>
            @endif
        </div>

    </form>

    <p class="conteo-resultados">
        @if ($total === 0)
            Ninguna reparación cobrada coincide con el filtro.
        @elseif ($total > $tickets->count())
            Mostrando las {{ $tickets->count() }} más recientes de {{ $total }}. Acota el filtro para ver otras.
        @else
            {{ $total }} {{ $total === 1 ? 'reparación cobrada' : 'reparaciones cobradas' }}.
        @endif
    </p>

    @if ($tickets->count())
        <div class="encabezado-historial">
            <span class="fila-fecha">Cobrado</span>
            <span class="fila-datos">Reparación y hotel</span>
            <span class="fila-acciones">Acciones</span>
        </div>
    @endif

    @foreach ($tickets as $ticket)
        <div class="fila-historial">

            <div class="fila-fecha">
                <strong>{{ $ticket->updated_at->format('d/m') }}</strong>
                <span>{{ $ticket->updated_at->format('Y') }}</span>
            </div>

            <div class="fila-datos">
                <span class="fila-titulo">{{ $ticket->titulo }}</span>
                <span class="fila-nota">{{ $ticket->hotel->nombre }} · creado por {{ $ticket->usuario->nombre_usuario }}</span>
            </div>

            <div class="fila-acciones">
                <a class="boton-secundario boton-chico" href="{{ route('reparaciones.show', $ticket) }}"
                   title="Ver la observación y quién lo movió">Ver</a>
            </div>

        </div>
    @endforeach

    @if ($total === 0)
        <p class="caja-vacia">
            @if ($hotelId || $desde || $hasta)
                Prueba quitando alguno de los filtros.
            @else
                Todavía no hay reparaciones cobradas.
            @endif
        </p>
    @endif

</div>

@include('partials.footer')
