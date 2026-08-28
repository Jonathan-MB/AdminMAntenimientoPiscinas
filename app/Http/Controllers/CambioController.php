<?php

namespace App\Http\Controllers;

use App\Models\Jornada;
use Illuminate\Http\Request;

class CambioController extends Controller
{
    //  Que se corrigio de esta jornada despues de guardarla
    public function index(Request $request, Jornada $jornada)
    {
        $jornada->load('hotel', 'usuario');

        $cambios = $jornada->cambios()
            ->with('usuario')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('cambios', compact('jornada', 'cambios'));
    }
}
