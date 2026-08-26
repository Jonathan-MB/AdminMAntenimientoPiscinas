<?php

namespace App\Http\Controllers;

use App\Http\Requests\IniciarSesionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccesoController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('panel');
        }

        return view('login');
    }



    //  Entrar con nombre de usuario y contrasena
    public function iniciar(IniciarSesionRequest $request)
    {
        $datos = $request->validated();

        $credenciales = [
            'nombre_usuario' => $datos['nombre_usuario'],
            'password'       => $datos['password'],
            'activo'         => true,
        ];

        if (! Auth::attempt($credenciales, $request->boolean('recordarme'))) {
            return redirect()->back()
                ->withInput($request->only('nombreUsuario'))
                ->with('error', 'Usuario o contrasena incorrectos');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('panel'));
    }



    public function cerrar(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('mensajeAlerta', 'Cerraste sesion');
    }
}
