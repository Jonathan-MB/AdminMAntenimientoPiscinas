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
        $hoteles = Hotel::withCount('piscinas')
            ->with(['rondasProgramadas' => function ($consulta) {
                $consulta->orderBy('orden')->orderBy('nombre');
            }])
            ->orderBy('nombre')
            ->get();

        return view('hoteles', compact('hoteles'));
    }



    public function store(StoreHotelRequest $request)
    {
        Hotel::create($request->validated());

        return redirect()->back()->with('mensajeCreado', 'Hotel creado correctamente');
    }



    public function show(Request $request, Hotel $hotel)
    {
        $porOrden = function ($consulta) {
            $consulta->orderBy('orden')->orderBy('nombre');
        };

        $hotel->load(['piscinas' => $porOrden, 'rondasProgramadas' => $porOrden]);

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
