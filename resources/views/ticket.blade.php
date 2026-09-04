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
                <p class="subtitulo-ticket">{{ $ticket->cliente }}</p>

                @if ($ticket->direccion)
                    {{-- El boton nace oculto: si no hay JavaScript no copia nada,
                         y la direccion se puede seleccionar igual con el dedo --}}
                    <p class="linea-direccion">
                        <span id="textoDireccion">{{ $ticket->direccion }}</span>

                        <button class="boton-copiar" type="button" id="botonCopiar" hidden
                                data-direccion="{{ $ticket->direccion }}"
                                title="Copiar la dirección para pegarla donde quieras">Copiar</button>

                        {{-- Si el navegador niega el portapapeles, por aqui se llega
                             al mapa igual, y de un toque en el telefono --}}
                        <a class="boton-copiar" target="_blank" rel="noopener"
                           href="https://www.google.com/maps/search/?api=1&query={{ urlencode($ticket->direccion) }}"
                           title="Abrir esta dirección en Google Maps">Ver en el mapa</a>
                    </p>
                @endif
            </div>

            <span class="pastilla-estado pastilla-{{ $ticket->estado }}">{{ $ticket->etiqueta_estado }}</span>
        </div>

        @include('partials.mensaje')

        @if ($errors->any())
            <div class="mensaje mensaje-error">{{ $errors->first() }}</div>
        @endif

        @if ($ticket->observacion)
            <div class="tarjeta-detalle">
                <h2 class="titulo-bloque">Observación</h2>
                <p class="texto-observacion">{{ $ticket->observacion }}</p>
            </div>
        @endif

        {{-- --------------------FOTOS------------------- --}}

        <h2 class="titulo-bloque">Fotos de la reparación</h2>

        @if ($ticket->fotos->isEmpty())
            <p class="nota-formulario nota-suelta">Todavía no hay fotos de este ticket.</p>
        @else
            <div class="galeria-fotos">
                @foreach ($ticket->fotos as $foto)
                    <figure class="marco-foto" data-foto="{{ $foto->id }}">

                        <img class="miniatura-foto"
                             src="{{ route('fotos.ver', $foto) }}"
                             alt="{{ $foto->nombre_original }}"
                             data-grande="{{ route('fotos.ver', $foto) }}"
                             loading="lazy">

                        @if (auth()->user()->tieneRol(\App\Models\Rol::MASTER, \App\Models\Rol::JEFE) || $foto->usuario_id === auth()->id())
                            <button class="boton-quitar-foto" type="button"
                                    data-id="{{ $foto->id }}"
                                    title="Quitar esta foto">&times;</button>
                        @endif

                    </figure>
                @endforeach
            </div>
        @endif

        <form class="formulario-fotos" method="POST" action="{{ route('fotos.store', $ticket) }}"
              enctype="multipart/form-data">
            @csrf

            <span class="titulo-elemento">Agregar fotos</span>

            {{-- Dos campos y no uno: con "capture" el telefono abre solo la
                 camara y se pierde la galeria, asi que van por separado --}}
            <div class="linea-fotos">
                <label class="boton-foto">
                    <input type="file" name="fotos[]" id="fotoCamara"
                           accept="image/*" capture="environment">
                    <span class="icono-foto" aria-hidden="true">&#128247;</span>
                    Tomar foto
                </label>

                <label class="boton-foto">
                    <input type="file" name="fotos[]" id="fotoGaleria"
                           accept="image/*" multiple>
                    <span class="icono-foto" aria-hidden="true">&#128194;</span>
                    Elegir de la galería
                </label>
            </div>

            <p class="elegidas" id="fotosElegidas"></p>

            <p class="nota-formulario">
                Hasta {{ \App\Models\FotoTicket::MAXIMO_POR_TICKET }} fotos por ticket,
                de {{ \App\Models\FotoTicket::MAXIMO_KB / 1024 }} MB cada una. JPG, PNG o WEBP.
            </p>

            {{-- Nace habilitado: si no hay JavaScript, el boton tiene que servir
                 igual. Es el JavaScript el que lo apaga hasta elegir algo. --}}
            <button class="boton-primario" type="submit" id="botonSubirFotos">Subir</button>
        </form>

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

    {{-- --------------------VISOR DE LA FOTO------------------- --}}

    <div class="fondo-visor" id="fondoVisor">
        <img class="foto-grande" id="fotoGrande" src="" alt="">
    </div>

</div>

<script>
    const rutaFotos = '{{ url('/reparaciones/foto') }}';
</script>

<script src="@recurso('js/ticket.js')"></script>

@include('partials.footer')
