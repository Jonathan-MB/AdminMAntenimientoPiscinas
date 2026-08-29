@if ($paginador->hasPages())

    @php
        //  Una ventana de paginas alrededor de la actual, con la primera y la
        //  ultima siempre a la vista
        $actual = $paginador->currentPage();
        $ultima = $paginador->lastPage();
        $desdePagina = max(1, $actual - 2);
        $hastaPagina = min($ultima, $actual + 2);
    @endphp

    <nav class="paginacion" aria-label="Páginas de resultados">

        @if ($paginador->onFirstPage())
            <span class="pagina pagina-apagada" aria-hidden="true">‹ Anterior</span>
        @else
            <a class="pagina" href="{{ $paginador->previousPageUrl() }}" rel="prev">‹ Anterior</a>
        @endif

        <span class="numeros-pagina">
            @if ($desdePagina > 1)
                <a class="pagina" href="{{ $paginador->url(1) }}">1</a>

                @if ($desdePagina > 2)
                    <span class="salto-pagina">…</span>
                @endif
            @endif

            @foreach (range($desdePagina, $hastaPagina) as $numero)
                @if ($numero === $actual)
                    <span class="pagina pagina-actual" aria-current="page">{{ $numero }}</span>
                @else
                    <a class="pagina" href="{{ $paginador->url($numero) }}">{{ $numero }}</a>
                @endif
            @endforeach

            @if ($hastaPagina < $ultima)
                @if ($hastaPagina < $ultima - 1)
                    <span class="salto-pagina">…</span>
                @endif

                <a class="pagina" href="{{ $paginador->url($ultima) }}">{{ $ultima }}</a>
            @endif
        </span>

        @if ($paginador->hasMorePages())
            <a class="pagina" href="{{ $paginador->nextPageUrl() }}" rel="next">Siguiente ›</a>
        @else
            <span class="pagina pagina-apagada" aria-hidden="true">Siguiente ›</span>
        @endif

    </nav>

@endif
