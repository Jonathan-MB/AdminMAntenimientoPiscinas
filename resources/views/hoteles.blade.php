@include('partials.head')
<link rel="stylesheet" href="@recurso('css/hoteles.css')">
<title>Hoteles</title>

@include('partials.header')

<div class="contenedor-general">
    <h1 class="vista-titulo">Hoteles</h1>

    @include('partials.mensaje')

    @if ($errors->any())
        <div class="mensaje mensaje-error">{{ $errors->first() }}</div>
    @endif

    <div class="linea-acciones">
        <button class="boton-primario" type="button" id="botonAbrirCrear">Crear hotel</button>
    </div>

    <div class="caja-tabla">
        <table class="tabla-hoteles">
            <thead>
                <tr>
                    <th>Hotel</th>
                    <th>Contacto</th>
                    <th>Rondas</th>
                    <th>Piscinas</th>
                    <th>Estado</th>
                    <th class="columna-acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($hoteles as $hotel)
                    <tr data-hotel="{{ $hotel->id }}">
                        <td data-titulo="Hotel">
                            <a class="enlace-hotel" href="{{ route('hoteles.show', $hotel) }}">{{ $hotel->nombre }}</a>
                        </td>
                        <td data-titulo="Contacto">{{ $hotel->contacto ?: '—' }}</td>
                        <td data-titulo="Rondas">
                            @forelse ($hotel->rondasProgramadas as $ronda)
                                <span class="pastilla-ronda">{{ $ronda->nombre }} {{ \Illuminate\Support\Str::substr($ronda->hora, 0, 5) }}</span>
                            @empty
                                <span class="texto-vacio">Sin rondas</span>
                            @endforelse
                        </td>
                        <td data-titulo="Piscinas">{{ $hotel->piscinas_count }}</td>
                        <td data-titulo="Estado">
                            <span class="etiqueta-estado {{ $hotel->activo ? 'etiqueta-activo' : 'etiqueta-inactivo' }}">
                                {{ $hotel->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td data-titulo="Acciones" class="columna-acciones">
                            <a class="boton-secundario boton-chico" href="{{ route('diario.index', $hotel) }}">Diario</a>
                            <a class="boton-secundario boton-chico" href="{{ route('hoteles.show', $hotel) }}">Editar</a>
                            <button class="boton-eliminar" type="button"
                                    data-id="{{ $hotel->id }}"
                                    data-nombre="{{ $hotel->nombre }}">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="texto-vacio">Aún no hay hoteles registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- --------------------POP UP CREAR------------------- --}}

    <div class="fondo-popup" id="fondoPopupCrear">
        <div class="popup">

            <h2 class="titulo-popup">Crear hotel</h2>

            <form method="POST" action="{{ route('hoteles.store') }}">
                @csrf

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="nombre">Nombre del hotel</label>
                    <input class="campo-formulario" type="text" id="nombre" name="nombre"
                           value="{{ old('nombre') }}" maxlength="120" required>
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="contacto">Persona de contacto</label>
                    <input class="campo-formulario" type="text" id="contacto" name="contacto"
                           value="{{ old('contacto') }}" maxlength="120">
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="telefono">Teléfono</label>
                    <input class="campo-formulario" type="text" id="telefono" name="telefono"
                           value="{{ old('telefono') }}" maxlength="45">
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="direccion">Dirección</label>
                    <input class="campo-formulario" type="text" id="direccion" name="direccion"
                           value="{{ old('direccion') }}" maxlength="150">
                </div>

                <p class="nota-formulario">Las rondas se configuran después, al editar el hotel.</p>

                <div class="linea-botones-popup">
                    <button class="boton-secundario" type="button" id="botonCerrarCrear">Cancelar</button>
                    <button class="boton-primario" type="submit">Guardar</button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    const rutaHoteles = '/hoteles';
</script>

<script src="@recurso('js/hoteles.js')"></script>

@include('partials.footer')
