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

        @if (auth()->user()->tieneRol(\App\Models\Rol::MASTER, \App\Models\Rol::ADMINISTRADOR, \App\Models\Rol::HOTEL))
            <a class="boton-secundario" href="{{ route('diario.index', ['hotel' => $jornada->hotel, 'fecha' => $jornada->fecha->format('Y-m-d')]) }}">Ver el diario</a>
        @endif
    </div>

    @include('partials.mensaje')

    @unless ($editable)
        <div class="mensaje mensaje-alerta">
            Esta jornada no es de hoy, así que quedó cerrada. Si hay que corregir algo, lo hace un administrador.
        </div>
    @endunless

    <div class="reja-jornada">

        {{-- --------------------TARJETA 1: LA JORNADA------------------- --}}

        <section class="tarjeta-grande">

            <div class="cabecera-tarjeta">
                <h2 class="titulo-tarjeta sin-borde">La jornada</h2>

                @if ($editable)
                    <span class="estado-guardado" id="estadoGuardado">Se guarda solo</span>
                @endif
            </div>

            <h3 class="titulo-bloque">Lecturas del metro de agua</h3>

            @forelse ($jornada->hotel->metrosAgua as $metro)
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="metro{{ $metro->id }}">{{ $metro->nombre }}</label>
                    <input class="campo-formulario campo-metro" type="number" step="0.01" min="0"
                           id="metro{{ $metro->id }}" data-metro="{{ $metro->id }}"
                           value="{{ $lecturas[$metro->id] ?? '' }}"
                           @disabled(! $editable)>
                </div>
            @empty
                <p class="texto-vacio">Este hotel no tiene metros de agua configurados. Pide a un administrador que los agregue.</p>
            @endforelse

            <h3 class="titulo-bloque">Listado de trabajo</h3>

            <p class="nota-formulario nota-suelta">Marca cada tarea a medida que la completes. Se guarda sola.</p>

            {{-- --------------------CONTADOR DEL LISTADO------------------- --}}

            <div class="marcador-tareas" id="marcadorTareas" data-total="{{ $tareas->count() }}">

                <div class="marcador-cifras">
                    <span class="marcador-dato marcador-hechas">
                        <strong id="cuentaHechas">0</strong> hechas
                    </span>

                    <span class="marcador-dato marcador-faltan" id="bloqueFaltan">
                        <strong id="cuentaFaltan">0</strong> por marcar
                    </span>
                </div>

                <div class="barra-avance">
                    <span class="barra-avance-relleno" id="avanceTareas"></span>
                </div>

            </div>

            <div class="bloque-tareas">
                @foreach ($tareas as $tarea)
                    <label class="linea-tarea @if (in_array($tarea->id, $marcadas)) tarea-hecha @else tarea-falta @endif">
                        <input type="checkbox" class="casilla-tarea"
                               data-tarea="{{ $tarea->id }}"
                               @checked(in_array($tarea->id, $marcadas))
                               @disabled(! $editable)>
                        <span class="texto-tarea">{{ $tarea->descripcion }}</span>
                    </label>
                @endforeach
            </div>

            <h3 class="titulo-bloque">Materiales y químicos sacados</h3>

            <p class="nota-formulario nota-suelta">Anota qué sacaste de almacén durante la jornada.</p>

            <div class="elemento-formulario">
                <textarea class="campo-formulario" id="materialesSacados" rows="4" maxlength="2000"
                          placeholder="2 galones de ácido muriático, 1 caja de tabletas…"
                          @disabled(! $editable)>{{ $jornada->materiales_sacados }}</textarea>
            </div>

        </section>

        {{-- --------------------TARJETA 2: LAS PISCINAS------------------- --}}

        <section class="tarjeta-grande">

            <h2 class="titulo-tarjeta">Piscinas</h2>

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

        </section>

    </div>

</div>

<script>
    const rutaJornada = '{{ url('/jornada/' . $jornada->id) }}';
</script>

<script src="@recurso('js/jornada.js')"></script>

@include('partials.footer')
