@include('partials.head')
<link rel="stylesheet" href="@recurso('css/medicion.css')">
<title>{{ $piscina->nombre }} · {{ $rondaProgramada->nombre }}</title>

@include('partials.header')

<div class="contenedor-general">

  <div class="contenedor-captura">

    <a class="enlace-volver" href="{{ route('registro.jornada', $jornada) }}">← Volver a la jornada</a>

    <div class="cabecera-medicion">
        <div>
            <h1 class="vista-titulo sin-borde">{{ $piscina->nombre }}</h1>
            <p class="contexto-medicion">
                {{ $jornada->hotel->nombre }} ·
                {{ $rondaProgramada->nombre }} {{ \Illuminate\Support\Str::substr($rondaProgramada->hora, 0, 5) }} ·
                {{ $jornada->fecha->format('d/m/Y') }}
            </p>
        </div>

        <div class="lado-cabecera">
            <span class="contador-piscinas" title="Piscina {{ $posicion + 1 }} de las {{ $piscinas->count() }} activas de este hotel">Piscina {{ $posicion + 1 }} de {{ $piscinas->count() }}</span>

            @if ($editable)
                <span class="estado-guardado" id="estadoGuardado">Se guarda solo</span>
            @endif
        </div>
    </div>

    @include('partials.mensaje')

    @if ($errors->any())
        <div class="mensaje mensaje-error">{{ $errors->first() }}</div>
    @endif

    @unless ($editable)
        <div class="mensaje mensaje-alerta">
            Esta jornada quedó cerrada. Puedes consultarla, pero no modificarla.
        </div>
    @endunless

    <form id="formularioMedicion" method="POST"
          action="{{ route('registro.medicion.store', ['jornada' => $jornada, 'rondaProgramada' => $rondaProgramada, 'piscina' => $piscina]) }}">
        @csrf

        {{-- --------------------LECTURAS------------------- --}}

        <h2 class="titulo-seccion">Pruebas del agua</h2>

        <div class="rejilla-lecturas">
            @php
                $lecturas = [
                    ['clLibre',        'Cloro libre',      'cl_libre',        '0.01'],
                    ['clTotal',        'Cloro total',      'cl_total',        '0.01'],
                    ['clCombinado',    'Combinado',        'cl_combinado',    '0.01'],
                    ['ph',             'pH',               'ph',              '0.01'],
                    ['alcalinidad',    'Alcalinidad',      'alcalinidad',     '1'],
                    ['durezaCalcio',   'Dureza de calcio', 'dureza_calcio',   '1'],
                    ['acidoCianurico', 'Ácido cianúrico',  'acido_cianurico', '1'],
                ];
            @endphp

            @foreach ($lecturas as $lectura)
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="{{ $lectura[0] }}">{{ $lectura[1] }}</label>
                    <input class="campo-formulario campo-numero" type="number" step="{{ $lectura[3] }}" min="0"
                           id="{{ $lectura[0] }}" name="{{ $lectura[0] }}" inputmode="decimal"
                           value="{{ old($lectura[0], $medicion?->{$lectura[2]}) }}"
                           data-original="{{ $medicion?->{$lectura[2]} }}"
                           data-etiqueta="{{ $lectura[1] }}"
                           @disabled(! $editable)>
                </div>
            @endforeach
        </div>

        {{-- --------------------QUIMICOS------------------- --}}

        <h2 class="titulo-seccion">Químicos aplicados</h2>

        <p class="nota-formulario nota-suelta">Deja en blanco lo que no se aplicó.</p>

        <div class="rejilla-quimicos">
            @foreach ($productos as $producto)
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="producto{{ $producto->id }}">
                        {{ $producto->nombre }}
                        <span class="unidad">{{ $producto->unidad }}</span>
                    </label>
                    <input class="campo-formulario campo-numero" type="number" step="0.01" min="0"
                           id="producto{{ $producto->id }}" name="dosis[{{ $producto->id }}]" inputmode="decimal"
                           value="{{ old('dosis.' . $producto->id, $cantidades[$producto->id] ?? '') }}"
                           data-original="{{ $cantidades[$producto->id] ?? '' }}"
                           data-etiqueta="{{ $producto->nombre }}"
                           @disabled(! $editable)>
                </div>
            @endforeach
        </div>

        {{-- --------------------OTROS------------------- --}}

        <h2 class="titulo-seccion">Filtro y observaciones</h2>

        <div class="tarjeta-otros">

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="nivelAgua">Nivel del agua</label>
                <select class="campo-formulario" id="nivelAgua" name="nivelAgua" @disabled(! $editable)>
                    @foreach (\App\Models\Medicion::niveles() as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('nivelAgua', $medicion?->nivel_agua ?? 'normal') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>

            <label class="interruptor">
                <input type="checkbox" name="retrolavado" value="1"
                       @checked(old('retrolavado', $medicion?->retrolavado))
                       @disabled(! $editable)>
                Se hizo retrolavado del filtro
            </label>

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="observacion">Observación del técnico</label>
                <textarea class="campo-formulario" id="observacion" name="observacion" rows="2" maxlength="255"
                          @disabled(! $editable)>{{ old('observacion', $medicion?->observacion) }}</textarea>
            </div>
        </div>

        {{-- --------------------BOTONES------------------- --}}

        @if ($editable)
            <noscript>
                <div class="linea-guardar">
                    <button class="boton-primario boton-grande" type="submit">Guardar</button>
                </div>
            </noscript>
        @endif
    </form>

    {{-- --------------------NAVEGACION ENTRE PISCINAS------------------- --}}

    <div class="linea-salida">
        <a class="boton-primario boton-grande" href="{{ route('registro.jornada', $jornada) }}">
            Listo, volver a las piscinas
        </a>

        @if ($siguiente)
            <a class="boton-secundario boton-grande" href="{{ route('registro.medicion', ['jornada' => $jornada, 'rondaProgramada' => $rondaProgramada, 'piscina' => $siguiente]) }}">
                Seguir con {{ $siguiente->nombre }}
            </a>
        @endif
    </div>

    <div class="linea-navegacion">
        @if ($anterior)
            <a class="enlace-navegacion" href="{{ route('registro.medicion', ['jornada' => $jornada, 'rondaProgramada' => $rondaProgramada, 'piscina' => $anterior]) }}">
                ← {{ $anterior->nombre }}
            </a>
        @else
            <span></span>
        @endif
    </div>

  </div>

</div>

<script src="@recurso('js/medicion.js')"></script>

@include('partials.footer')
