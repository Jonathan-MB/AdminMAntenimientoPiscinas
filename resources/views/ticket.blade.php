@include('partials.head')
<link rel="stylesheet" href="@recurso('css/reparaciones.css')">
<title>{{ $ticket->titulo }}</title>

@include('partials.header')

<div class="contenedor-general">

    <div class="contenedor-medio">

        <a class="enlace-volver" href="{{ route('reparaciones.index') }}">← Volver a reparaciones</a>

        <div class="linea-titulo">
            <div>
                <h1 class="vista-titulo sin-borde">{{ $ticket->titulo }}</h1>
                <p class="subtitulo-ticket">{{ $ticket->hotel->nombre }}</p>
            </div>

            <span class="pastilla-estado pastilla-{{ $ticket->estado }}">{{ $ticket->etiqueta_estado }}</span>
        </div>

        @include('partials.mensaje')

        @if ($ticket->observacion)
            <div class="tarjeta-detalle">
                <h2 class="titulo-bloque">Observación</h2>
                <p class="texto-observacion">{{ $ticket->observacion }}</p>
            </div>
        @endif

        <h2 class="titulo-bloque">Quién lo movió y cuándo</h2>

        <p class="nota-formulario nota-suelta">
            Cada cambio de estado queda registrado, incluida la creación.
        </p>

        @foreach ($ticket->movimientos as $movimiento)
            <div class="linea-movimiento">

                <div class="movimiento-estados">
                    @if ($movimiento->etiqueta_anterior)
                        <span class="pastilla-estado pastilla-{{ $movimiento->estado_anterior }}">{{ $movimiento->etiqueta_anterior }}</span>
                        <span class="flecha-movimiento">→</span>
                    @else
                        <span class="marca-creacion">Creado</span>
                    @endif

                    <span class="pastilla-estado pastilla-{{ $movimiento->estado_nuevo }}">{{ $movimiento->etiqueta_nueva }}</span>
                </div>

                <span class="movimiento-cuando">
                    {{ $movimiento->created_at->format('d/m/Y H:i') }} · {{ $movimiento->usuario->nombre_usuario }}
                </span>

            </div>
        @endforeach

    </div>

</div>

@include('partials.footer')
