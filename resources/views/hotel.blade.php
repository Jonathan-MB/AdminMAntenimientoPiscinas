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

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="horaRondaManana">Ronda mañana</label>
                <input class="campo-formulario" type="time" id="horaRondaManana"
                       value="{{ \Illuminate\Support\Str::substr($hotel->hora_ronda_manana, 0, 5) }}">
            </div>

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="horaRondaTarde">Ronda tarde</label>
                <input class="campo-formulario" type="time" id="horaRondaTarde"
                       value="{{ \Illuminate\Support\Str::substr($hotel->hora_ronda_tarde, 0, 5) }}">
            </div>
        </div>

        <div class="linea-estado">
            <label class="interruptor">
                <input type="checkbox" id="activo" @checked($hotel->activo)>
                Hotel activo
            </label>

            <button class="boton-primario" type="button" id="botonGuardarHotel">Guardar cambios</button>
        </div>

        <p class="nota-formulario">Las horas de ronda van en el horario de Aruba.</p>
    </div>

    {{-- --------------------PISCINAS------------------- --}}

    <div class="linea-titulo-piscinas">
        <h2 class="titulo-seccion">Piscinas</h2>
        <button class="boton-primario" type="button" id="botonAbrirCrear">Agregar piscina</button>
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
                            <button class="boton-secundario boton-chico boton-alternar" type="button"
                                    data-id="{{ $piscina->id }}"
                                    data-activa="{{ $piscina->activa ? 1 : 0 }}">
                                {{ $piscina->activa ? 'Desactivar' : 'Activar' }}
                            </button>

                            <button class="boton-eliminar" type="button"
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

    {{-- --------------------POP UP CREAR PISCINA------------------- --}}

    <div class="fondo-popup" id="fondoPopupCrear">
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
                    <label class="titulo-elemento" for="orden">Orden en el formato</label>
                    <input class="campo-formulario" type="number" id="orden" name="orden"
                           value="{{ old('orden') }}" min="0" max="999" placeholder="Al final">
                </div>

                <div class="linea-botones-popup">
                    <button class="boton-secundario" type="button" id="botonCerrarCrear">Cancelar</button>
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
</script>

<script src="{{ asset('js/hotel.js') }}"></script>

@include('partials.footer')
