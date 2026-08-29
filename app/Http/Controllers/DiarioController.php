<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Jornada;
use App\Models\Rol;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiarioController extends Controller
{
    public function index(Request $request, Hotel $hotel)
    {
        $this->verificarAcceso($hotel);

        //  Mes que se esta mirando en el calendario
        $mes = $this->mesPedido($request);

        //  Dia seleccionado: el que pidan, o el ultimo con datos, o hoy
        $fecha = $request->query('fecha');

        if ($fecha) {
            $seleccionado = Carbon::createFromFormat('Y-m-d', $fecha)->startOfDay();
        } else {
            $ultima = $hotel->jornadas()->orderByDesc('fecha')->first();
            $seleccionado = $ultima ? $ultima->fecha->copy() : Carbon::today();
        }

        $dias     = $this->diasConJornada($hotel, $mes);
        $calendario = $this->armarCalendario($mes, $dias);
        $jornada  = $this->traerJornada($hotel, $seleccionado);

        return view('diario', compact('hotel', 'mes', 'calendario', 'seleccionado', 'jornada', 'dias'));
    }



    //  Devuelve el detalle de un dia en JSON, para cambiar sin recargar
    public function dia(Request $request, Hotel $hotel, string $fecha)
    {
        $this->verificarAcceso($hotel);

        $dia = Carbon::createFromFormat('Y-m-d', $fecha)->startOfDay();
        $jornada = $this->traerJornada($hotel, $dia);

        if (! $jornada) {
            return response()->json([
                'fecha'  => $dia->format('Y-m-d'),
                'titulo' => $this->titularFecha($dia),
                'vacio'  => true,
            ], 200);
        }

        return response()->json([
            'fecha'  => $dia->format('Y-m-d'),
            'titulo' => $this->titularFecha($dia),
            'vacio'  => false,
            'metros'           => $this->metrosEnArreglo($jornada),
            'colaborador'      => $jornada->usuario->nombre_usuario,
            'rondas'           => $this->rondasEnArreglo($jornada),
        ], 200);
    }



    //  La hoja de un dia, para imprimir. La ve el hotel y el personal de AQUALIVE.
    public function imprimir(Request $request, Hotel $hotel, string $fecha)
    {
        $this->verificarAcceso($hotel);

        $dia     = Carbon::createFromFormat('Y-m-d', $fecha)->startOfDay();
        $jornada = $this->traerJornada($hotel, $dia);

        if (! $jornada) {
            abort(404, 'No hay registro de ese día');
        }

        $jornada->load(['lecturasMetro.metroAgua', 'tareasRealizadas.tarea', 'rondas.mediciones.piscina']);

        return view('impresion-dia', [
            'hotel'    => $hotel,
            'jornada'  => $jornada,
            'dia'      => $dia,
            'titulo'   => $this->titularFecha($dia),
            'impresa'  => Carbon::now(),
        ]);
    }



    //  El rol hotel solo ve su propio hotel
    private function verificarAcceso(Hotel $hotel): void
    {
        $usuario = Auth::user();

        if ($usuario->tieneRol(Rol::HOTEL) && $usuario->hotel_id !== $hotel->id) {
            abort(403, 'Solo puedes consultar tu propio hotel');
        }
    }



    private function mesPedido(Request $request): Carbon
    {
        $mes = $request->query('mes');

        if ($mes && preg_match('/^\d{4}-\d{2}$/', $mes)) {
            return Carbon::createFromFormat('Y-m-d', $mes . '-01')->startOfMonth();
        }

        return Carbon::today()->startOfMonth();
    }



    //  Dias del mes que tienen jornada, como ['2026-08-25' => true]
    private function diasConJornada(Hotel $hotel, Carbon $mes): array
    {
        return $hotel->jornadas()
            ->whereBetween('fecha', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->pluck('fecha')
            ->mapWithKeys(function ($fecha) {
                return [$fecha->format('Y-m-d') => true];
            })
            ->all();
    }



    //  Rejilla de semanas empezando en lunes
    private function armarCalendario(Carbon $mes, array $dias): array
    {
        $inicio = $mes->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $fin    = $mes->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $semanas = [];
        $semana  = [];
        $cursor  = $inicio->copy();

        while ($cursor <= $fin) {
            $clave = $cursor->format('Y-m-d');

            $semana[] = [
                'fecha'      => $clave,
                'numero'     => $cursor->day,
                'delMes'     => $cursor->month === $mes->month,
                'tieneDatos' => isset($dias[$clave]),
                'esHoy'      => $cursor->isToday(),
                'futuro'     => $cursor->isFuture(),
            ];

            if (count($semana) === 7) {
                $semanas[] = $semana;
                $semana = [];
            }

            $cursor->addDay();
        }

        return $semanas;
    }



    private function traerJornada(Hotel $hotel, Carbon $fecha)
    {
        return Jornada::with([
                'usuario',
                'lecturasMetro.metroAgua',
                'rondas.rondaProgramada',
                'rondas.mediciones.piscina',
                'rondas.mediciones.dosis.producto',
            ])
            ->where('hotel_id', $hotel->id)
            ->whereDate('fecha', $fecha)
            ->first();
    }



    private function rondasEnArreglo(Jornada $jornada): array
    {
        return $jornada->rondas
            ->sortBy(function ($ronda) {
                return $ronda->rondaProgramada->orden;
            })
            ->map(function ($ronda) {
                return [
                    'nombre'      => $ronda->rondaProgramada->nombre,
                    'hora'        => substr($ronda->hora, 0, 5),
                    'observacion' => $ronda->observacion,
                    'mediciones'  => $ronda->mediciones
                        ->sortBy(function ($medicion) {
                            return $medicion->piscina->orden;
                        })
                        ->map(function ($medicion) {
                            return [
                                'piscina'     => $medicion->piscina->nombre,
                                'clLibre'     => $medicion->cl_libre,
                                'clTotal'     => $medicion->cl_total,
                                'clCombinado' => $medicion->cl_combinado,
                                'ph'          => $medicion->ph,
                                'alcalinidad' => $medicion->alcalinidad,
                                'durezaCalcio' => $medicion->dureza_calcio,
                                'acidoCianurico' => $medicion->acido_cianurico,
                                'retrolavado' => $medicion->retrolavado,
                                'observacion' => $medicion->observacion,
                                'dosis'       => $medicion->dosis->map(function ($dosis) {
                                    return [
                                        'producto' => $dosis->producto->nombre,
                                        'cantidad' => $dosis->cantidad,
                                        'unidad'   => $dosis->producto->unidad,
                                    ];
                                })->values(),
                            ];
                        })->values(),
                ];
            })->values()->all();
    }



    private function metrosEnArreglo(Jornada $jornada): array
    {
        return $jornada->lecturasMetro
            ->sortBy(function ($lectura) {
                return $lectura->metroAgua->orden;
            })
            ->map(function ($lectura) {
                return [
                    'nombre'  => $lectura->metroAgua->nombre,
                    'lectura' => $lectura->lectura,
                ];
            })->values()->all();
    }



    private function titularFecha(Carbon $fecha): string
    {
        $dias   = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        $meses  = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                   'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

        return $dias[$fecha->dayOfWeek] . ' ' . $fecha->day . ' de ' . $meses[$fecha->month - 1] . ' de ' . $fecha->year;
    }
}
