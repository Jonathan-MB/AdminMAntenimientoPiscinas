@include('partials.head')
<link rel="stylesheet" href="@recurso('css/cambios.css')">
<title>Correcciones · {{ $jornada->fecha->format('d/m/Y') }}</title>

@include('partials.header')

<div class="contenedor-general">

    <div class="contenedor-medio">

        <a class="enlace-volver" href="{{ route('panel') }}">← Volver al panel</a>

        <h1 class="vista-titulo">Correcciones</h1>

        <p class="subtitulo-cambios">
            {{ $jornada->hotel->nombre }} · {{ $jornada->fecha->format('d/m/Y') }} ·
            registró {{ $jornada->usuario->nombre_usuario }}
        </p>

        @include('partials.mensaje')

        <p class="nota-formulario nota-suelta">
            Valores que se modificaron <strong>después</strong> de haber quedado guardados.
            Llenar un campo vacío por primera vez no aparece aquí: eso es captura, no corrección.
        </p>

        @forelse ($cambios as $cambio)
            <div class="tarjeta-cambio">

                <div class="cabecera-cambio">
                    <span class="donde-cambio">{{ $cambio->donde }}</span>
                    <span class="cuando-cambio">
                        {{ $cambio->created_at->format('d/m/Y H:i') }} · {{ $cambio->usuario->nombre_usuario }}
                    </span>
                </div>

                <div class="cuerpo-cambio">
                    <span class="campo-cambio">{{ $cambio->campo }}</span>

                    <div class="comparacion">
                        <span class="valor-antes" title="Lo que había antes">{{ $cambio->valor_anterior }}</span>
                        <span class="flecha-cambio">→</span>
                        <span class="valor-despues" title="Lo que quedó">{{ $cambio->valor_nuevo ?? 'sin valor' }}</span>
                    </div>
                </div>

            </div>
        @empty
            <p class="caja-vacia">Esta jornada no tiene correcciones: todo quedó como se capturó la primera vez.</p>
        @endforelse

    </div>

</div>

@include('partials.footer')
