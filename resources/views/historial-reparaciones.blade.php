@include('partials.head')
<link rel="stylesheet" href="@recurso('css/reparaciones.css')">
<title>Historial de reparaciones</title>

@include('partials.header')

<div class="contenedor-general">

    <div class="linea-titulo">
        <h1 class="vista-titulo sin-borde">Reparaciones terminadas</h1>

        <a class="boton-secundario" href="{{ route('reparaciones.index') }}">← Volver al tablero</a>
    </div>

    @include('partials.mensaje')

    <form class="barra-filtros" method="GET" action="{{ route('reparaciones.historial') }}">

        <div class="elemento-filtro">
            <label class="titulo-elemento" for="cliente">Cliente</label>
            <input class="campo-formulario" type="text" id="cliente" name="cliente"
                   value="{{ $cliente }}" maxlength="150" placeholder="Nombre del cliente">
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

            @if ($cliente || $desde || $hasta)
                <a class="boton-secundario" href="{{ route('reparaciones.historial') }}">Limpiar</a>
            @endif
        </div>

    </form>

    <p class="conteo-resultados">
        @if ($total === 0)
            Ninguna reparación terminada coincide con el filtro.
        @elseif ($total > $tickets->count())
            Mostrando las {{ $tickets->count() }} más recientes de {{ $total }}. Acota el filtro para ver otras.
        @else
            {{ $total }} {{ $total === 1 ? 'reparación terminada' : 'reparaciones terminadas' }}.
        @endif
    </p>

    @if ($tickets->count())
        <div class="encabezado-historial">
            <span class="fila-fecha">Terminada</span>
            <span class="fila-datos">Reparación y cliente</span>
            <span class="fila-estado">Cómo terminó</span>
            <span class="fila-acciones">Acciones</span>
        </div>
    @endif

    @foreach ($tickets as $ticket)
        <div class="fila-historial">

            <div class="fila-fecha">
                <strong>{{ $ticket->cerrado_en->format('d/m') }}</strong>
                <span>{{ $ticket->cerrado_en->format('Y') }}</span>
            </div>

            <div class="fila-datos">
                <span class="fila-titulo">{{ $ticket->titulo }}</span>
                <span class="fila-nota">{{ $ticket->cliente }} · creado por {{ $ticket->usuario->nombre_usuario }}</span>
            </div>

            <div class="fila-estado">
                <span class="pastilla-estado pastilla-{{ $ticket->estado }}">{{ $ticket->etiqueta_estado }}</span>
            </div>

            <div class="fila-acciones">
                <a class="boton-secundario boton-chico" href="{{ route('reparaciones.show', $ticket) }}"
                   title="Ver la observación y quién lo movió">Ver</a>
            </div>

        </div>
    @endforeach

    @if ($total === 0)
        <p class="caja-vacia">
            @if ($cliente || $desde || $hasta)
                Prueba quitando alguno de los filtros.
            @else
                Todavía no hay reparaciones terminadas.
            @endif
        </p>
    @endif

</div>

@include('partials.footer')
