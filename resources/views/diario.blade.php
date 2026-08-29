@include('partials.head')
<link rel="stylesheet" href="@recurso('css/diario.css')">
<title>Diario · {{ $hotel->nombre }}</title>

@include('partials.header')

<div class="contenedor-general">

    <h1 class="vista-titulo">{{ $hotel->nombre }}</h1>

    @include('partials.mensaje')

    <div class="reja-diario">

        {{-- --------------------CALENDARIO------------------- --}}

        <aside class="panel-calendario">

            <div class="linea-mes">
                <a class="boton-mes" href="{{ route('diario.index', ['hotel' => $hotel, 'mes' => $mes->copy()->subMonth()->format('Y-m')]) }}"
                   aria-label="Mes anterior">‹</a>

                <span class="nombre-mes">
                    {{ ucfirst(['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][$mes->month - 1]) }}
                    {{ $mes->year }}
                </span>

                <a class="boton-mes" href="{{ route('diario.index', ['hotel' => $hotel, 'mes' => $mes->copy()->addMonth()->format('Y-m')]) }}"
                   aria-label="Mes siguiente">›</a>
            </div>

            <div class="rejilla-dias">
                <span class="cabecera-dia">L</span>
                <span class="cabecera-dia">M</span>
                <span class="cabecera-dia">X</span>
                <span class="cabecera-dia">J</span>
                <span class="cabecera-dia">V</span>
                <span class="cabecera-dia">S</span>
                <span class="cabecera-dia">D</span>

                @foreach ($calendario as $semana)
                    @foreach ($semana as $dia)
                        <button class="celda-dia
                                       @if (! $dia['delMes']) celda-fuera @endif
                                       @if ($dia['tieneDatos']) celda-con-datos @endif
                                       @if ($dia['esHoy']) celda-hoy @endif
                                       @if ($dia['fecha'] === $seleccionado->format('Y-m-d')) celda-elegida @endif"
                                type="button"
                                data-fecha="{{ $dia['fecha'] }}"
                                @disabled($dia['futuro'])>
                            {{ $dia['numero'] }}
                        </button>
                    @endforeach
                @endforeach
            </div>

            <div class="leyenda">
                <span class="punto-leyenda punto-con-datos"></span> Con registro
                <span class="punto-leyenda punto-hoy"></span> Hoy
            </div>

        </aside>

        {{-- --------------------DETALLE DEL DIA------------------- --}}

        <section class="panel-dia" id="panelDia">

            <div class="cabecera-dia-detalle">
                <h2 class="titulo-dia" id="tituloDia">
                    @php
                        $dias  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
                        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
                    @endphp
                    {{ ucfirst($dias[$seleccionado->dayOfWeek]) }} {{ $seleccionado->day }} de {{ $meses[$seleccionado->month - 1] }} de {{ $seleccionado->year }}
                </h2>

                <div class="acciones-dia">
                    <a class="boton-imprimir @if (! $jornada) boton-apagado @endif"
                       id="botonImprimirDia"
                       href="{{ route('diario.imprimir', ['hotel' => $hotel, 'fecha' => $seleccionado->format('Y-m-d')]) }}"
                       target="_blank" rel="noopener"
                       title="Abre la hoja del día lista para imprimir">Imprimir el día</a>

                    <div class="cargando" id="cargando">Cargando…</div>
                </div>
            </div>

            <div id="contenidoDia">

                @if (! $jornada)
                    <p class="sin-registro">No hay registro de este día.</p>
                @else
                    <div class="resumen-jornada">
                        <div class="dato-resumen">
                            <span class="titulo-elemento">Metros de agua</span>
                            <strong>
                                @forelse ($jornada->lecturasMetro->sortBy(fn ($l) => $l->metroAgua->orden) as $lectura)
                                    {{ $lectura->metroAgua->nombre }}: {{ $lectura->lectura }}@if (! $loop->last) · @endif
                                @empty
                                    —
                                @endforelse
                            </strong>
                        </div>

                        <div class="dato-resumen">
                            <span class="titulo-elemento">Registró</span>
                            <strong>{{ $jornada->usuario->nombre_usuario }}</strong>
                        </div>

                        <div class="dato-resumen">
                            <span class="titulo-elemento">Rondas</span>
                            <strong>{{ $jornada->rondas->count() }}</strong>
                        </div>
                    </div>

                    @foreach ($jornada->rondas->sortBy(fn ($r) => $r->rondaProgramada->orden) as $ronda)
                        <div class="bloque-ronda">
                            <div class="cabecera-ronda">
                                <h3 class="nombre-ronda">{{ $ronda->rondaProgramada->nombre }}</h3>
                                <span class="hora-ronda">{{ \Illuminate\Support\Str::substr($ronda->hora, 0, 5) }}</span>
                            </div>

                            @foreach ($ronda->mediciones->sortBy(fn ($m) => $m->piscina->orden) as $medicion)
                                <div class="tarjeta-piscina">
                                    <div class="cabecera-piscina">
                                        <span class="nombre-piscina">{{ $medicion->piscina->nombre }}</span>

                                        @if ($medicion->retrolavado)
                                            <span class="marca-retrolavado">Retrolavado</span>
                                        @endif
                                    </div>

                                    <span class="etiqueta-bloque">Pruebas del agua</span>

                                    <div class="rejilla-lecturas">
                                        <div class="lectura"><span>Cl libre</span><strong>{{ $medicion->cl_libre ?? '—' }}</strong></div>
                                        <div class="lectura"><span>Cl total</span><strong>{{ $medicion->cl_total ?? '—' }}</strong></div>
                                        <div class="lectura"><span>Combinado</span><strong>{{ $medicion->cl_combinado ?? '—' }}</strong></div>
                                        <div class="lectura"><span>pH</span><strong>{{ $medicion->ph ?? '—' }}</strong></div>
                                        <div class="lectura"><span>Alcalinidad</span><strong>{{ $medicion->alcalinidad ?? '—' }}</strong></div>
                                        <div class="lectura"><span>Dureza</span><strong>{{ $medicion->dureza_calcio ?? '—' }}</strong></div>
                                        <div class="lectura"><span>Cianúrico</span><strong>{{ $medicion->acido_cianurico ?? '—' }}</strong></div>
                                    </div>

                                    @if ($medicion->dosis->count())
                                        <span class="etiqueta-bloque">Químicos aplicados</span>

                                        <div class="linea-dosis">
                                            @foreach ($medicion->dosis as $dosis)
                                                <span class="pastilla-dosis">{{ $dosis->producto->nombre }} · {{ $dosis->cantidad }} {{ $dosis->producto->unidad }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($medicion->observacion)
                                        <span class="etiqueta-bloque">Observación del técnico</span>

                                        <p class="observacion-piscina">{{ $medicion->observacion }}</p>
                                    @endif
                                </div>
                            @endforeach

                            @if ($ronda->observacion)
                                <p class="observacion-ronda">
                                    <span class="etiqueta-observacion">Observación de la ronda</span>
                                    {{ $ronda->observacion }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                @endif

            </div>

        </section>

    </div>

</div>

<script>
    const hotelId = {{ $hotel->id }};
    const rutaDia = '{{ url('/diario/' . $hotel->id . '/dia') }}';
    const botonImprimirDia = document.getElementById('botonImprimirDia');
</script>

<script src="@recurso('js/diario.js')"></script>

@include('partials.footer')
