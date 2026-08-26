<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePiscinaRequest;
use App\Http\Requests\UpdatePiscinaRequest;
use App\Models\Hotel;
use App\Models\Piscina;
use Illuminate\Http\Request;

class PiscinaController extends Controller
{
    public function store(StorePiscinaRequest $request, Hotel $hotel)
    {
        $datos = $request->validated();

        //  Si no mandan orden, va al final
        if (! isset($datos['orden'])) {
            $datos['orden'] = (int) $hotel->piscinas()->max('orden') + 1;
        }

        $datos['hotel_id'] = $hotel->id;

        Piscina::create($datos);

        return redirect()->back()->with('mensajeCreado', 'Piscina creada correctamente');
    }



    public function update(UpdatePiscinaRequest $request, Piscina $piscina)
    {
        $data = $request->validated();

        // PATCH sin data
        if (empty($data)) {
            return response()->json([
                'message' => 'Sin datos'
            ], 422);
        }

        // Cargar datos sin guardar
        $piscina->fill($data);

        // No hubo cambios
        if (! $piscina->isDirty()) {
            return response()->json([
                'message' => 'No se detectaron cambios'
            ], 422);
        }

        $piscina->save();

        return response()->json([
            'message' => 'Actualizado Correctamente',
            'data'    => $piscina->fresh()
        ], 200);
    }



    public function destroy(Request $request, Piscina $piscina)
    {
        //  Si ya tiene mediciones, borrarla dejaria huecos en el historico
        $mediciones = $piscina->mediciones()->count();

        if ($mediciones > 0) {
            return response()->json([
                'message' => "No se puede eliminar: la piscina tiene $mediciones medicion(es) registrada(s). Desactivala en vez de eliminarla."
            ], 409);
        }

        $piscina->delete();

        return response()->json([
            'message' => 'Piscina eliminada correctamente'
        ], 200);
    }
}
