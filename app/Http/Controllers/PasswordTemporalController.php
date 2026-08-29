<?php

namespace App\Http\Controllers;

use App\Http\Requests\CambiarPasswordTemporalRequest;
use App\Models\CambioPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasswordTemporalController extends Controller
{
    public function index(Request $request)
    {
        //  Sin la marca no hay nada que hacer aqui
        if (! Auth::user()->debe_cambiar_password) {
            return redirect()->route('panel');
        }

        return view('password-temporal');
    }



    public function update(CambiarPasswordTemporalRequest $request)
    {
        $usuario = Auth::user();

        $usuario->password = $request->validated()['password'];
        $usuario->debe_cambiar_password = false;
        $usuario->save();

        CambioPassword::anotar($usuario->id, $usuario->id);

        //  Se renueva la sesion: la clave anterior la conocia alguien mas
        $request->session()->regenerate();

        return redirect()->route('panel')->with('mensajeActualizado', 'Tu contraseña quedó cambiada. Ya nadie más la conoce.');
    }
}
