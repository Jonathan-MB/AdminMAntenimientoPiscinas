<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'rol'               => \App\Http\Middleware\VerificarRol::class,
            'password.temporal' => \App\Http\Middleware\ExigirCambioPassword::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //  Nunca mostrar la pagina de error en blanco (419, 404, 500...):
        //  para el usuario es mas claro volver al panel, que de paso lo
        //  manda solo al login si ya no hay sesion (asi cierra el caso del
        //  419 al salir, que es una sesion que ya expiro).
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return $response;
            }

            $codigo = $response->getStatusCode();

            if ($e instanceof TokenMismatchException || in_array($codigo, [404, 405, 419, 500, 503], true)) {
                return redirect()->route('panel')->with('error', 'Ocurrió un problema, intenta de nuevo.');
            }

            return $response;
        });
    })->create();
