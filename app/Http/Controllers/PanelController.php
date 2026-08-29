<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Jornada;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();

        //  Cada rol entra directo a lo suyo, no a mirar sus propios datos.
        //  Con varios roles manda el mas amplio: quien ademas administra ve el
        //  panel, no la pantalla de registro.
        if (! $usuario->tieneRol(Rol::MASTER, Rol::ADMINISTRADOR)) {

            if ($usuario->tieneRol(Rol::COLABORADOR)) {
                return redirect()->route('registro.index');
            }

            if ($usuario->tieneRol(Rol::HOTEL)) {
                if (! $usuario->hotel_id) {
                    return view('panel', [
                        'jornadas'  => collect(),
                        'hoteles'   => collect(),
                        'empleados' => collect(),
                        'sinHotel'  => true,
                    ]);
                }

                return redirect()->route('diario.index', $usuario->hotel_id);
            }

            //  Va de ultimo a proposito: quien ademas captura jornadas entra a
            //  capturarlas. Quien solo repara, entra al tablero.
            if ($usuario->tieneRol(Rol::JEFE, Rol::REPARACION)) {
                return redirect()->route('reparaciones.index');
            }
        }

        //  Master y administrador: las jornadas de todos los hoteles, filtrables
        $hotelId    = $request->query('hotel');
        $empleadoId = $request->query('empleado');
        $desde      = $request->query('desde');
        $hasta      = $request->query('hasta');

        $consulta = Jornada::with([
                'usuario',
                'hotel' => function ($c) {
                    $c->withCount([
                        'piscinas' => function ($p) {
                            $p->where('activa', true);
                        },
                        'rondasProgramadas' => function ($p) {
                            $p->where('activa', true);
                        },
                    ]);
                },
                'rondas' => function ($c) {
                    $c->withCount('mediciones');
                },
            ])
            ->withCount('cambios');

        if ($hotelId) {
            $consulta->where('hotel_id', $hotelId);
        }

        if ($empleadoId) {
            $consulta->where('usuario_id', $empleadoId);
        }

        if ($desde) {
            $consulta->whereDate('fecha', '>=', $desde);
        }

        if ($hasta) {
            $consulta->whereDate('fecha', '<=', $hasta);
        }

        $total = (clone $consulta)->count();

        $jornadas = $consulta->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $hoteles = Hotel::orderBy('nombre')->get();

        //  Solo quienes de verdad han registrado alguna jornada
        $empleados = Usuario::whereIn('id', Jornada::select('usuario_id')->distinct())
            ->orderBy('nombre_usuario')
            ->get();

        return view('panel', compact('jornadas', 'hoteles', 'empleados', 'total', 'hotelId', 'empleadoId', 'desde', 'hasta'));
    }
}
