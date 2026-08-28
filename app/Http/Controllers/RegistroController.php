<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbrirJornadaRequest;
use App\Http\Requests\UpdateJornadaRequest;
use App\Models\Hotel;
use App\Models\Jornada;
use App\Models\Rol;
use App\Models\Tarea;
use App\Models\TareaRealizada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistroController extends Controller
{
    public function index(Request $request)
    {
        $hoteles = Hotel::where('activo', true)->orderBy('nombre')->get();

        //  Las ultimas jornadas, para retomar sin buscar
        $recientes = Jornada::with('hotel')
            ->orderByDesc('fecha')
            ->limit(8)
            ->get();

        return view('registro', compact('hoteles', 'recientes'));
    }



    public function store(AbrirJornadaRequest $request)
    {
        $datos = $request->validated();

        $jornada = Jornada::firstOrCreate(
            ['hotel_id' => $datos['hotel_id'], 'fecha' => $datos['fecha']],
            ['usuario_id' => Auth::id()]
        );

        return redirect()->route('registro.jornada', $jornada);
    }



    public function show(Request $request, Jornada $jornada)
    {
        $usuario = Auth::user();
        $jornada->load([
            'hotel.piscinas' => function ($consulta) {
                $consulta->where('activa', true)->orderBy('orden')->orderBy('nombre');
            },
            'hotel.rondasProgramadas' => function ($consulta) {
                $consulta->where('activa', true)->orderBy('orden')->orderBy('nombre');
            },
            'usuario',
            'rondas.mediciones',
            'tareasRealizadas',
        ]);

        //  Cuantas piscinas ya tienen medicion en cada ronda
        $hechas = [];

        foreach ($jornada->rondas as $ronda) {
            $hechas[$ronda->ronda_programada_id] = $ronda->mediciones->pluck('piscina_id')->all();
        }

        $tareas  = Tarea::where('activa', true)->orderBy('orden')->get();
        $marcadas = $jornada->tareasRealizadas->where('hecha', true)->pluck('tarea_id')->all();

        $editable = $jornada->puedeEditarla($usuario);

        return view('jornada', compact('jornada', 'hechas', 'tareas', 'marcadas', 'editable'));
    }



    public function update(UpdateJornadaRequest $request, Jornada $jornada)
    {
        if (! $jornada->puedeEditarla(Auth::user())) {
            return response()->json([
                'message' => 'Esta jornada ya no se puede editar. Pide a un administrador que la corrija.'
            ], 403);
        }

        $data = $request->validated();

        // PATCH sin data
        if (empty($data)) {
            return response()->json([
                'message' => 'Sin datos'
            ], 422);
        }

        // Cargar datos sin guardar
        $jornada->fill($data);

        // No hubo cambios
        if (! $jornada->isDirty()) {
            return response()->json([
                'message' => 'No se detectaron cambios'
            ], 422);
        }

        $jornada->save();

        return response()->json([
            'message' => 'Actualizado Correctamente',
            'data'    => $jornada->fresh()
        ], 200);
    }



    //  Marcar o desmarcar una tarea del listado de trabajo
    public function marcarTarea(Request $request, Jornada $jornada, Tarea $tarea)
    {
        if (! $jornada->puedeEditarla(Auth::user())) {
            return response()->json([
                'message' => 'Esta jornada ya no se puede editar'
            ], 403);
        }

        $hecha = $request->boolean('hecha');

        TareaRealizada::updateOrCreate(
            ['jornada_id' => $jornada->id, 'tarea_id' => $tarea->id],
            ['hecha' => $hecha, 'marcada_en' => $hecha ? now() : null]
        );

        return response()->json([
            'message' => $hecha ? 'Tarea marcada' : 'Tarea desmarcada'
        ], 200);
    }
}
