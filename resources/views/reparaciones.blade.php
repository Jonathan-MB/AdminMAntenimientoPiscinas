@include('partials.head')
<link rel="stylesheet" href="@recurso('css/reparaciones.css')">
<title>Reparaciones</title>

@include('partials.header')

<div class="contenedor-general">

    <div class="linea-titulo">
        <h1 class="vista-titulo sin-borde">Reparaciones</h1>

        <div class="botones-titulo">
            <a class="boton-secundario" href="{{ route('reparaciones.historial') }}">Historial</a>
            <button class="boton-primario" type="button" id="botonAbrirCrear">Crear ticket</button>
        </div>
    </div>

    @include('partials.mensaje')

    @if ($errors->any())
        <div class="mensaje mensaje-error">{{ $errors->first() }}</div>
    @endif

    <div class="tablero">

        @foreach (\App\Models\Ticket::estadosAbiertos() as $estado)
            @php $delEstado = $tickets[$estado] ?? collect(); @endphp

            <section class="columna-estado columna-{{ $estado }}">

                <div class="cabecera-columna">
                    <h2 class="nombre-estado">{{ \App\Models\Ticket::estados()[$estado] }}</h2>
                    <span class="cuenta-estado">{{ $delEstado->count() }}</span>
                </div>

                @forelse ($delEstado as $ticket)
                    <article class="tarjeta-ticket" data-ticket="{{ $ticket->id }}">

                        <a class="titulo-ticket" href="{{ route('reparaciones.show', $ticket) }}">{{ $ticket->titulo }}</a>

                        <span class="hotel-ticket">{{ $ticket->hotel->nombre }}</span>

                        <span class="pie-ticket">
                            {{ $ticket->created_at->format('d/m/Y') }} · {{ $ticket->usuario->nombre_usuario }}
                        </span>

                        <div class="acciones-ticket">
                            <label class="titulo-elemento" for="estado{{ $ticket->id }}">Mover a</label>
                            <select class="campo-formulario campo-estado" id="estado{{ $ticket->id }}"
                                    data-ticket="{{ $ticket->id }}">
                                @foreach (\App\Models\Ticket::estados() as $valor => $texto)
                                    <option value="{{ $valor }}" @selected($ticket->estado === $valor)>{{ $texto }}</option>
                                @endforeach
                            </select>

                            @if (auth()->user()->tieneRol(\App\Models\Rol::MASTER, \App\Models\Rol::JEFE))
                                <button class="boton-eliminar boton-eliminar-ticket" type="button"
                                        data-id="{{ $ticket->id }}"
                                        data-titulo="{{ $ticket->titulo }}">Eliminar</button>
                            @endif
                        </div>

                    </article>
                @empty
                    <p class="columna-vacia">Nada aquí.</p>
                @endforelse

            </section>
        @endforeach

    </div>

    {{-- --------------------POP UP CREAR------------------- --}}

    <div class="fondo-popup" id="fondoPopupCrear">
        <div class="popup">

            <h2 class="titulo-popup">Crear ticket</h2>

            <form method="POST" action="{{ route('reparaciones.store') }}">
                @csrf

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="hotelId">Hotel</label>
                    <select class="campo-formulario" id="hotelId" name="hotelId" required>
                        <option value="">Elige el hotel</option>
                        @foreach ($hoteles as $hotel)
                            <option value="{{ $hotel->id }}" @selected(old('hotelId') == $hotel->id)>{{ $hotel->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="titulo">Qué hay que reparar</label>
                    <input class="campo-formulario" type="text" id="titulo" name="titulo"
                           value="{{ old('titulo') }}" maxlength="120"
                           placeholder="Bomba del spa hace ruido" required>
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="observacion">Observación</label>
                    <textarea class="campo-formulario" id="observacion" name="observacion" rows="4"
                              maxlength="2000">{{ old('observacion') }}</textarea>
                </div>

                <p class="nota-formulario">El ticket empieza en «Por hacer».</p>

                <div class="linea-botones-popup">
                    <button class="boton-secundario" type="button" id="botonCerrarCrear">Cancelar</button>
                    <button class="boton-primario" type="submit">Crear</button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    const rutaReparaciones = '{{ url('/reparaciones') }}';
</script>

<script src="@recurso('js/reparaciones.js')"></script>

@include('partials.footer')
