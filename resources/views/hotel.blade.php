@include('partials.head')
<link rel="stylesheet" href="{{ asset('css/hotel.css') }}">
<title>{{ $hotel->nombre }}</title>

@include('partials.header')

<div class="contenedor-general">

    <a class="enlace-volver" href="{{ route('hoteles.index') }}">← Volver a hoteles</a>

    <h1 class="vista-titulo">{{ $hotel->nombre }}</h1>

    @include('partials.mensaje')

    @if ($errors->any())
        <div class="mensaje mensaje-error">{{ $errors->first() }}</div>
    @endif

    {{-- --------------------DATOS DEL HOTEL------------------- --}}

    <div class="tarjeta-hotel">
        <h2 class="titulo-seccion">Datos del hotel</h2>

        <div class="rejilla-datos">
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="nombre">Nombre</label>
                <input class="campo-formulario" type="text" id="nombre" value="{{ $hotel->nombre }}" maxlength="120">
            </div>

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="contacto">Persona de contacto</label>
                <input class="campo-formulario" type="text" id="contacto" value="{{ $hotel->contacto }}" maxlength="120">
            </div>

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="telefono">Teléfono</label>
                <input class="campo-formulario" type="text" id="telefono" value="{{ $hotel->telefono }}" maxlength="45">
            </div>

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="direccion">Dirección</label>
                <input class="campo-formulario" type="text" id="direccion" value="{{ $hotel->direccion }}" maxlength="150">
            </div>
        </div>

        <div class="linea-estado">
            <label class="interruptor">
                <input type="checkbox" id="activo" @checked($hotel->activo)>
                Hotel activo
            </label>

            <button class="boton-primario" type="button" id="botonGuardarHotel">Guardar cambios</button>
        </div>
    </div>

    {{-- --------------------RONDAS------------------- --}}

    <div class="linea-titulo-seccion">
        <h2 class="titulo-seccion">Rondas del día</h2>
        <button class="boton-primario" type="button" id="botonAbrirRonda">Agregar ronda</button>
    </div>

    <p class="nota-formulario nota-suelta">Cada hotel define sus propias rondas. Las horas van en el horario de Aruba.</p>

    <div class="caja-tabla">
        <table class="tabla-rondas">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Ronda</th>
                    <th>Hora</th>
                    <th>Estado</th>
                    <th class="columna-acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($hotel->rondasProgramadas as $ronda)
                    <tr data-ronda="{{ $ronda->id }}">
                        <td data-titulo="Orden">
                            <input class="campo-tabla campo-orden" type="number" value="{{ $ronda->orden }}" min="0" max="999">
                        </td>
                        <td data-titulo="Ronda">
                            <input class="campo-tabla campo-nombre" type="text" value="{{ $ronda->nombre }}" maxlength="45">
                        </td>
                        <td data-titulo="Hora">
                            <input class="campo-tabla campo-hora" type="time"
                                   value="{{ \Illuminate\Support\Str::substr($ronda->hora, 0, 5) }}">
                        </td>
                        <td data-titulo="Estado">
                            <span class="etiqueta-estado {{ $ronda->activa ? 'etiqueta-activo' : 'etiqueta-inactivo' }}">
                                {{ $ronda->activa ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td data-titulo="Acciones" class="columna-acciones">
                            <button class="boton-secundario boton-chico boton-guardar-ronda" type="button"
                                    data-id="{{ $ronda->id }}">Guardar</button>

                            <button class="boton-secundario boton-chico boton-alternar-ronda" type="button"
                                    data-id="{{ $ronda->id }}"
                                    data-activa="{{ $ronda->activa ? 1 : 0 }}">
                                {{ $ronda->activa ? 'Desactivar' : 'Activar' }}
                            </button>

                            <button class="boton-eliminar boton-eliminar-ronda" type="button"
                                    data-id="{{ $ronda->id }}"
                                    data-nombre="{{ $ronda->nombre }}">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="texto-vacio">Este hotel aún no tiene rondas configuradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- --------------------PISCINAS------------------- --}}

    <div class="linea-titulo-seccion">
        <h2 class="titulo-seccion">Piscinas</h2>
        <button class="boton-primario" type="button" id="botonAbrirPiscina">Agregar piscina</button>
    </div>

    <div class="caja-tabla">
        <table class="tabla-piscinas">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Piscina</th>
                    <th>Estado</th>
                    <th class="columna-acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($hotel->piscinas as $piscina)
                    <tr data-piscina="{{ $piscina->id }}">
                        <td data-titulo="Orden">{{ $piscina->orden }}</td>
                        <td data-titulo="Piscina">{{ $piscina->nombre }}</td>
                        <td data-titulo="Estado">
                            <span class="etiqueta-estado {{ $piscina->activa ? 'etiqueta-activo' : 'etiqueta-inactivo' }}">
                                {{ $piscina->activa ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td data-titulo="Acciones" class="columna-acciones">
                            <button class="boton-secundario boton-chico boton-alternar-piscina" type="button"
                                    data-id="{{ $piscina->id }}"
                                    data-activa="{{ $piscina->activa ? 1 : 0 }}">
                                {{ $piscina->activa ? 'Desactivar' : 'Activar' }}
                            </button>

                            <button class="boton-eliminar boton-eliminar-piscina" type="button"
                                    data-id="{{ $piscina->id }}"
                                    data-nombre="{{ $piscina->nombre }}">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="texto-vacio">Este hotel aún no tiene piscinas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- --------------------POP UP AGREGAR RONDA------------------- --}}

    <div class="fondo-popup" id="fondoPopupRonda">
        <div class="popup">

            <h2 class="titulo-popup">Agregar ronda</h2>

            <form method="POST" action="{{ route('rondas.store', $hotel) }}">
                @csrf

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="nombreRonda">Nombre de la ronda</label>
                    <input class="campo-formulario" type="text" id="nombreRonda" name="nombre"
                           value="{{ old('nombre') }}" maxlength="45" placeholder="Mediodía" required>
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="horaRonda">Hora</label>
                    <input class="campo-formulario" type="time" id="horaRonda" name="hora"
                           value="{{ old('hora') }}" required>
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="ordenRonda">Orden</label>
                    <input class="campo-formulario" type="number" id="ordenRonda" name="orden"
                           value="{{ old('orden') }}" min="0" max="999" placeholder="Al final">
                </div>

                <div class="linea-botones-popup">
                    <button class="boton-secundario" type="button" id="botonCerrarRonda">Cancelar</button>
                    <button class="boton-primario" type="submit">Guardar</button>
                </div>
            </form>

        </div>
    </div>

    {{-- --------------------POP UP AGREGAR PISCINA------------------- --}}

    <div class="fondo-popup" id="fondoPopupPiscina">
        <div class="popup">

            <h2 class="titulo-popup">Agregar piscina</h2>

            <form method="POST" action="{{ route('piscinas.store', $hotel) }}">
                @csrf

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="nombrePiscina">Nombre de la piscina</label>
                    <input class="campo-formulario" type="text" id="nombrePiscina" name="nombre"
                           value="{{ old('nombre') }}" maxlength="45" placeholder="BIG POOL" required>
                </div>

                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="ordenPiscina">Orden en el formato</label>
                    <input class="campo-formulario" type="number" id="ordenPiscina" name="orden"
                           value="{{ old('orden') }}" min="0" max="999" placeholder="Al final">
                </div>

                <div class="linea-botones-popup">
                    <button class="boton-secundario" type="button" id="botonCerrarPiscina">Cancelar</button>
                    <button class="boton-primario" type="submit">Guardar</button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    const hotelId = {{ $hotel->id }};
    const rutaHoteles = '/hoteles';
    const rutaPiscinas = '/piscinas';
    const rutaRondas = '/rondas';
</script>

<script src="{{ asset('js/hotel.js') }}"></script>

@include('partials.footer')
