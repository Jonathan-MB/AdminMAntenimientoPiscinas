<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SuplantacionController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExigirCambioPassword
{
    //  Rutas que tienen que seguir pasando, o el usuario se queda encerrado
    private const LIBRES = [
        'password.temporal.index',
        'password.temporal.update',
        'acceso.cerrar',
        'suplantacion.terminar',
    ];


    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();

        if (! $usuario || ! $usuario->debe_cambiar_password) {
            return $next($request);
        }

        //  Suplantando no se exige: la marca es del usuario que se esta viendo,
        //  no de quien mira, y cambiarle la clave desde ahi seria un enredo
        if ($request->session()->has(SuplantacionController::LLAVE)) {
            return $next($request);
        }

        if ($request->routeIs(self::LIBRES)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tienes que elegir una contraseña propia antes de seguir'
            ], 403);
        }

        return redirect()->route('password.temporal.index');
    }
}
