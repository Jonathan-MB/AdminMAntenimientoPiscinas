<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHotelRequest;
use App\Http\Requests\UpdateHotelRequest;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function index(Request $request)
    {
        $hoteles = Hotel::withCount('piscinas')->orderBy('nombre')->get();

        return view('hoteles', compact('hoteles'));
    }



    public function store(StoreHotelRequest $request)
    {
        Hotel::create($request->validated());

        return redirect()->back()->with('mensajeCreado', 'Hotel creado correctamente');
    }



    public function show(Request $request, Hotel $hotel)
    {
        $hotel->load(['piscinas' => function ($consulta) {
            $consulta->orderBy('orden')->orderBy('nombre');
        }]);

        return view('hotel', compact('hotel'));
    }



    public function update(UpdateHotelRequest $request, Hotel $hotel)
    {
        $data = $request->validated();

        // PATCH sin data
        if (empty($data)) {
            return response()->json([
                'message' => 'Sin datos'
            ], 422);
        }

        //  El formulario manda 05:00 y la base guarda 05:00:00. Sin igualar el
        //  formato, isDirty() siempre da verdadero y guarda sin que haya cambios.
        foreach (['hora_ronda_manana', 'hora_ronda_tarde'] as $campo) {
            if (isset($data[$campo]) && strlen($data[$campo]) === 5) {
                $data[$campo] .= ':00';
            }
        }

        // Cargar datos sin guardar
        $hotel->fill($data);

        // No hubo cambios
        if (! $hotel->isDirty()) {
            return response()->json([
                'message' => 'No se detectaron cambios'
            ], 422);
        }

        $hotel->save();

        return response()->json([
            'message' => 'Actualizado Correctamente',
            'data'    => $hotel->fresh()
        ], 200);
    }



    public function destroy(Request $request, Hotel $hotel)
    {
        //  Las llaves foraneas son restrictOnDelete: avisamos antes de que reviente
        $piscinas = $hotel->piscinas()->count();
        $jornadas = $hotel->jornadas()->count();
        $usuarios = $hotel->usuarios()->count();

        if ($piscinas > 0 || $jornadas > 0 || $usuarios > 0) {
            return response()->json([
                'message' => "No se puede eliminar: el hotel tiene $piscinas piscina(s), $jornadas jornada(s) y $usuarios usuario(s). Desactivalo en vez de eliminarlo."
            ], 409);
        }

        $hotel->delete();

        return response()->json([
            'message' => 'Hotel eliminado correctamente'
        ], 200);
    }
}
