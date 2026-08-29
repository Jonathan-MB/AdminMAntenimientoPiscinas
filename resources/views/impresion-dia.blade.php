@include('partials.head-impresion')
<title>{{ $hotel->nombre }} · {{ $dia->format('d-m-Y') }}</title>
</head>

<body>

{{-- --------------------BARRA QUE NO SE IMPRIME------------------- --}}

<div class="barra-pantalla">
    <a class="boton-volver-hoja" href="{{ route('diario.index', ['hotel' => $hotel, 'fecha' => $dia->format('Y-m-d')]) }}">← Volver al diario</a>
    <button class="boton-imprimir-hoja" type="button" id="botonImprimir">Imprimir esta hoja</button>
</div>

<main class="hoja">

    {{-- --------------------MEMBRETE------------------- --}}

    <header class="membrete">

        <div class="membrete-marca">
            <img class="membrete-logo" src="@recurso('img/logo-400.png')" alt="AQUALIVE Pool Technology">

            <div class="membrete-datos">
                <span class="membrete-linea">Control de mantenimiento de piscinas</span>
                <span class="membrete-linea">Aruba</span>
            </div>
        </div>

        <div class="membrete-cliente">
            <span class="rotulo">Hotel</span>
            <strong class="nombre-cliente">{{ $hotel->nombre }}</strong>

            @if ($hotel->direccion)
                <span class="membrete-linea">{{ $hotel->direccion }}</span>
            @endif

            @if ($hotel->contacto || $hotel->telefono)
                <span class="membrete-linea">
                    {{ $hotel->contacto }}@if ($hotel->contacto && $hotel->telefono) · @endif{{ $hotel->telefono }}
                </span>
            @endif
        </div>

    </header>

    <div class="titulo-hoja">
        <h1>Revisión del {{ $titulo }}</h1>

        <div class="firma-hoja">
            <span><span class="rotulo">Registró</span> {{ $jornada->usuario->nombre_usuario }}</span>
            <span><span class="rotulo">Rondas</span> {{ $jornada->rondas->count() }}</span>
        </div>
    </div>

    {{-- --------------------RESUMEN DE LA JORNADA------------------- --}}

    <section class="bloque">

        <h2 class="titulo-bloque-hoja">Resumen de la jornada</h2>

        <table class="tabla-resumen">
            <tbody>
                <tr>
                    <th scope="row">Metros de agua</th>
                    <td>
                        @forelse ($jornada->lecturasMetro->sortBy(fn ($l) => $l->metroAgua->orden) as $lectura)
                            <span class="dato-metro">{{ $lectura->metroAgua->nombre }}: <strong>{{ $lectura->lectura }}</strong></span>
                        @empty
                            Sin lectura
                        @endforelse
                    </td>
                </tr>

                <tr>
                    <th scope="row">Materiales sacados</th>
                    <td>{{ $jornada->materiales_sacados ?: 'Ninguno' }}</td>
                </tr>
            </tbody>
        </table>

    </section>

    {{-- --------------------RONDAS------------------- --}}

    @foreach ($jornada->rondas->sortBy(fn ($r) => $r->rondaProgramada->orden) as $ronda)

        <section class="bloque bloque-ronda-hoja">

            <h2 class="titulo-bloque-hoja">
                {{ $ronda->rondaProgramada->nombre }}
                <span class="hora-hoja">{{ \Illuminate\Support\Str::substr($ronda->hora, 0, 5) }}</span>
            </h2>

            <table class="tabla-mediciones">
                <thead>
                    <tr>
                        <th class="col-piscina" scope="col">Piscina</th>
                        <th scope="col">Cl libre<span class="unidad-col">ppm</span></th>
                        <th scope="col">Cl total<span class="unidad-col">ppm</span></th>
                        <th scope="col">Cl comb.<span class="unidad-col">ppm</span></th>
                        <th scope="col">pH</th>
                        <th scope="col">Alcalinidad<span class="unidad-col">ppm</span></th>
                        <th scope="col">Dureza Ca<span class="unidad-col">ppm</span></th>
                        <th scope="col">Ác. cianúrico<span class="unidad-col">ppm</span></th>
                        <th scope="col">Nivel</th>
                        <th scope="col">Retro.</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($ronda->mediciones->sortBy(fn ($m) => $m->piscina->orden) as $medicion)

                        <tr class="fila-medicion">
                            <th class="col-piscina" scope="row">{{ $medicion->piscina->nombre }}</th>
                            <td>{{ $medicion->cl_libre ?? '—' }}</td>
                            <td>{{ $medicion->cl_total ?? '—' }}</td>
                            <td>{{ $medicion->cl_combinado ?? '—' }}</td>
                            <td>{{ $medicion->ph ?? '—' }}</td>
                            <td>{{ $medicion->alcalinidad ?? '—' }}</td>
                            <td>{{ $medicion->dureza_calcio ?? '—' }}</td>
                            <td>{{ $medicion->acido_cianurico ?? '—' }}</td>
                            <td>{{ \App\Models\Medicion::niveles()[$medicion->nivel_agua] ?? '—' }}</td>
                            <td>{{ $medicion->retrolavado ? 'Sí' : 'No' }}</td>
                        </tr>

                        @if ($medicion->dosis->count() || $medicion->observacion)
                            <tr class="fila-detalle">
                                <td colspan="10">
                                    @if ($medicion->dosis->count())
                                        <span class="rotulo">Químicos aplicados</span>
                                        <span class="texto-detalle">
                                            @foreach ($medicion->dosis as $dosis)
                                                {{ $dosis->producto->nombre }} {{ $dosis->cantidad }} {{ $dosis->producto->unidad }}@if (! $loop->last) · @endif
                                            @endforeach
                                        </span>
                                    @endif

                                    @if ($medicion->observacion)
                                        <span class="rotulo">Observación</span>
                                        <span class="texto-detalle">{{ $medicion->observacion }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endif

                    @endforeach
                </tbody>
            </table>

            @if ($ronda->observacion)
                <p class="observacion-hoja">
                    <span class="rotulo">Observación de la ronda</span>
                    {{ $ronda->observacion }}
                </p>
            @endif

        </section>

    @endforeach

    {{-- --------------------LISTA DE TAREAS------------------- --}}

    @if ($jornada->tareasRealizadas->count())

        <section class="bloque">

            <h2 class="titulo-bloque-hoja">Trabajos del día</h2>

            <ul class="lista-tareas-hoja">
                @foreach ($jornada->tareasRealizadas->sortBy(fn ($t) => $t->tarea->orden) as $realizada)
                    <li class="tarea-hoja">
                        <span class="casilla @if ($realizada->hecha) casilla-marcada @endif">{{ $realizada->hecha ? '✓' : '' }}</span>
                        {{ $realizada->tarea->descripcion }}
                    </li>
                @endforeach
            </ul>

        </section>

    @endif

    {{-- --------------------PIE------------------- --}}

    <footer class="pie-hoja">
        <span>AQUALIVE · Pool Technology — Control de mantenimiento de piscinas</span>
        <span>Impreso el {{ $impresa->format('d/m/Y') }} a las {{ $impresa->format('H:i') }} (hora de Aruba)</span>
    </footer>

</main>

<script src="@recurso('js/impresion.js')"></script>

</body>

</html>
