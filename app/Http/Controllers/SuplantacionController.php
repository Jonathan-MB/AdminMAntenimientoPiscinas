<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuplantacionController extends Controller
{
    //  Marca en sesion que hay una suplantacion en curso
    public const LLAVE = 'suplantador_id';


    //  Solo el master llega aqui: la ruta ya lo filtra con el middleware de rol
    public function iniciar(Request $request, Usuario $usuario)
    {
        $actual = Auth::user();

        if ($actual->id === $usuario->id) {
            return redirect()->back()->with('error', 'Ya estás en tu propia cuenta');
        }

        if (! $usuario->activo) {
            return redirect()->back()->with('error', 'Ese usuario está inactivo. Actívalo antes de verlo.');
        }

        //  Una suplantacion dentro de otra dejaria sin rastro al master original
        if ($request->session()->has(self::LLAVE)) {
            return redirect()->back()->with('error', 'Ya estás viendo como otro usuario. Vuelve a tu cuenta primero.');
        }

        $request->session()->put(self::LLAVE, $actual->id);

        Auth::login($usuario);

        return redirect()->route('panel')->with('mensajeAlerta', 'Estás viendo la aplicación como ' . $usuario->nombre_usuario);
    }



    //  Volver no exige ser master: exige tener la marca en sesion
    public function terminar(Request $request)
    {
        $id = $request->session()->pull(self::LLAVE);

        if (! $id) {
            return redirect()->route('panel');
        }

        $master = Usuario::find($id);

        //  Si la cuenta original ya no sirve, se cierra sesion por completo
        if (! $master || ! $master->activo || ! $master->esMaster()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Tu cuenta original ya no está disponible');
        }

        Auth::login($master);

        return redirect()->route('usuarios.index')->with('mensajeAlerta', 'Volviste a tu cuenta');
    }
}
