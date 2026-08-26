<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    //  Se usa como 'rol:master,administrador'. Compara por nombre, nunca por id.
    public function handle(Request $request, Closure $siguiente, string ...$roles): Response
    {
        $usuario = Auth::user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        if (! $usuario->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Tu usuario esta inactivo');
        }

        if (! $usuario->tieneRol(...$roles)) {
            abort(403, 'No tienes permiso para entrar aqui');
        }

        return $siguiente($request);
    }
}
