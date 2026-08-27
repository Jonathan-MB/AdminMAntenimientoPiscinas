<?php

namespace App\Http\Controllers;

use App\Models\Jornada;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();

        //  Cada rol entra directo a lo suyo, no a mirar sus propios datos
        if ($usuario->tieneRol(Rol::COLABORADOR)) {
            return redirect()->route('registro.index');
        }

        if ($usuario->tieneRol(Rol::HOTEL)) {
            if (! $usuario->hotel_id) {
                return view('panel', ['jornadas' => collect(), 'sinHotel' => true]);
            }

            return redirect()->route('diario.index', $usuario->hotel_id);
        }

        //  Master y administrador: las ultimas jornadas de todos los hoteles
        $jornadas = Jornada::with([
                'usuario',
                'hotel' => function ($consulta) {
                    $consulta->withCount([
                        'piscinas' => function ($c) {
                            $c->where('activa', true);
                        },
                        'rondasProgramadas' => function ($c) {
                            $c->where('activa', true);
                        },
                    ]);
                },
                'rondas' => function ($consulta) {
                    $consulta->withCount('mediciones');
                },
            ])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('panel', compact('jornadas'));
    }
}
