<?php

namespace App\Http\Controllers;

use App\Http\Requests\CambiarPasswordRequest;
use App\Http\Requests\UpdatePerfilRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user()->load('roles', 'hotel');

        return view('perfil', compact('usuario'));
    }



    public function update(UpdatePerfilRequest $request)
    {
        $usuario = Auth::user();
        $usuario->fill($request->validated());

        if (! $usuario->isDirty()) {
            return redirect()->back()->with('mensajeAlerta', 'No cambiaste nada');
        }

        $usuario->save();

        return redirect()->back()->with('mensajeActualizado', 'Datos actualizados correctamente');
    }



    //  Cambiar la propia contrasena
    public function cambiarPassword(CambiarPasswordRequest $request)
    {
        $usuario = Auth::user();
        $usuario->password = $request->validated()['password'];
        $usuario->save();

        //  Al cambiar la clave se renueva la sesion, por si alguien mas la tenia
        $request->session()->regenerate();

        return redirect()->route('perfil.index')->with('mensajeActualizado', 'Tu contraseña quedó cambiada');
    }
}
