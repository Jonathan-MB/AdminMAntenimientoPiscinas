@auth
    @if (auth()->user()->tieneRol(\App\Models\Rol::MASTER, \App\Models\Rol::JEFE, \App\Models\Rol::REPARACION))

        {{-- --------------------AVISO DE REPARACIONES------------------- --}}

        <div class="aviso-reparaciones" id="avisoReparaciones">

            <span class="texto-aviso" id="textoAviso"></span>

            <div class="botones-aviso">
                <a class="boton-aviso" href="{{ route('reparaciones.index') }}">Ver el tablero</a>
                <button class="cerrar-aviso" type="button" id="botonCerrarAviso"
                        title="Cerrar el aviso">&times;</button>
            </div>

        </div>

        <script>
            const rutaResumen = '{{ route('reparaciones.resumen') }}';
        </script>

        <script src="@recurso('js/aviso-reparaciones.js')"></script>

    @endif
@endauth
