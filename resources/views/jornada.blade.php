@include('partials.head')
<link rel="stylesheet" href="@recurso('css/jornada.css')">
<title>Jornada {{ $jornada->fecha->format('d/m/Y') }}</title>

@include('partials.header')

<div class="contenedor-general">

    <a class="enlace-volver" href="{{ route('registro.index') }}">← Volver al registro</a>

    <div class="linea-titulo">
        <div>
            <h1 class="vista-titulo sin-borde">{{ $jornada->hotel->nombre }}</h1>
            <p class="subtitulo-jornada">{{ $jornada->fecha->format('d/m/Y') }}</p>
        </div>

        <a class="boton-secundario" href="{{ route('diario.index', ['hotel' => $jornada->hotel, 'fecha' => $jornada->fecha->format('Y-m-d')]) }}">Ver el diario</a>
    </div>

    @include('partials.mensaje')

    @unless ($editable)
        <div class="mensaje mensaje-alerta">
            Esta jornada no es de hoy, así que quedó cerrada. Si hay que corregir algo, lo hace un administrador.
        </div>
    @endunless

    {{-- --------------------METRO DE AGUA------------------- --}}

    <div class="tarjeta-metro">
        <div class="elemento-formulario">
            <label class="titulo-elemento" for="lecturaMetroAgua">Lectura del metro de agua</label>
            <input class="campo-formulario campo-metro" type="number" step="0.01" min="0"
                   id="lecturaMetroAgua" value="{{ $jornada->lectura_metro_agua }}"
                   @disabled(! $editable)>
        </div>

        @if ($editable)
            <button class="boton-secundario" type="button" id="botonGuardarMetro">Guardar</button>
        @endif
    </div>

    {{-- --------------------RONDAS------------------- --}}

    <h2 class="titulo-seccion">Rondas</h2>

    <p class="nota-formulario nota-suelta">
        Toca una piscina para registrar sus pruebas. Las que ya tienen registro quedan en verde.
    </p>

    @forelse ($jornada->hotel->rondasProgramadas as $programada)
        @php
            $piscinasHechas = $hechas[$programada->id] ?? [];
            $total = $jornada->hotel->piscinas->count();
        @endphp

        <div class="bloque-ronda">
            <div class="cabecera-ronda">
                <div>
                    <h3 class="nombre-ronda">{{ $programada->nombre }}</h3>
                    <span class="hora-ronda">{{ \Illuminate\Support\Str::substr($programada->hora, 0, 5) }}</span>
                </div>

                <span class="avance {{ count($piscinasHechas) === $total && $total > 0 ? 'avance-completo' : '' }}"
                      title="Piscinas con registro en esta ronda">
                    {{ count($piscinasHechas) }} de {{ $total }} piscinas
                </span>
            </div>

            <div class="lista-piscinas">
                @foreach ($jornada->hotel->piscinas as $piscina)
                    @php $hecha = in_array($piscina->id, $piscinasHechas); @endphp

                    <a class="tarjeta-piscina {{ $hecha ? 'piscina-hecha' : '' }}"
                       href="{{ route('registro.medicion', ['jornada' => $jornada, 'rondaProgramada' => $programada, 'piscina' => $piscina]) }}">
                        <span class="marca-estado">{{ $hecha ? '✓' : '' }}</span>
                        <span class="nombre-piscina">{{ $piscina->nombre }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <p class="texto-vacio">Este hotel no tiene rondas configuradas. Pide a un administrador que las agregue.</p>
    @endforelse

    {{-- --------------------LISTADO DE TRABAJO------------------- --}}

    <h2 class="titulo-seccion">Listado de trabajo</h2>

    <p class="nota-formulario nota-suelta">
        Marca cada tarea a medida que la completes. Se guarda sola.
    </p>

    <div class="bloque-tareas">
        @foreach ($tareas as $tarea)
            <label class="linea-tarea">
                <input type="checkbox" class="casilla-tarea"
                       data-tarea="{{ $tarea->id }}"
                       @checked(in_array($tarea->id, $marcadas))
                       @disabled(! $editable)>
                <span>{{ $tarea->descripcion }}</span>
            </label>
        @endforeach
    </div>

</div>

<script>
    const jornadaId = {{ $jornada->id }};
    const rutaJornada = '{{ url('/jornada/' . $jornada->id) }}';
</script>

<script src="@recurso('js/jornada.js')"></script>

@include('partials.footer')
