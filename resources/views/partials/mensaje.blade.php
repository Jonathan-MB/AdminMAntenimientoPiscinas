@if (session('mensajeCreado'))
    <div class="mensaje mensaje-exito">{{ session('mensajeCreado') }}</div>
@endif

@if (session('mensajeActualizado'))
    <div class="mensaje mensaje-exito">{{ session('mensajeActualizado') }}</div>
@endif

@if (session('mensajeAlerta'))
    <div class="mensaje mensaje-alerta">{{ session('mensajeAlerta') }}</div>
@endif

@if (session('error'))
    <div class="mensaje mensaje-error">{{ session('error') }}</div>
@endif
