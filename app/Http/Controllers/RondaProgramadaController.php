<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRondaProgramadaRequest;
use App\Http\Requests\UpdateRondaProgramadaRequest;
use App\Models\Hotel;
use App\Models\RondaProgramada;
use Illuminate\Http\Request;

class RondaProgramadaController extends Controller
{
    public function store(StoreRondaProgramadaRequest $request, Hotel $hotel)
    {
        $datos = $request->validated();

        //  Si no mandan orden, va al final
        if (! isset($datos['orden'])) {
            $datos['orden'] = (int) $hotel->rondasProgramadas()->max('orden') + 1;
        }

        $datos['hotel_id'] = $hotel->id;

        RondaProgramada::create($datos);

        return redirect()->back()->with('mensajeCreado', 'Ronda creada correctamente');
    }



    public function update(UpdateRondaProgramadaRequest $request, RondaProgramada $rondaProgramada)
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
        if (isset($data['hora']) && strlen($data['hora']) === 5) {
            $data['hora'] .= ':00';
        }

        // Cargar datos sin guardar
        $rondaProgramada->fill($data);

        // No hubo cambios
        if (! $rondaProgramada->isDirty()) {
            return response()->json([
                'message' => 'No se detectaron cambios'
            ], 422);
        }

        $rondaProgramada->save();

        return response()->json([
            'message' => 'Actualizado Correctamente',
            'data'    => $rondaProgramada->fresh()
        ], 200);
    }



    public function destroy(Request $request, RondaProgramada $rondaProgramada)
    {
        //  Si ya se uso en alguna jornada, borrarla dejaria huecos en el historico
        $usos = $rondaProgramada->rondas()->count();

        if ($usos > 0) {
            return response()->json([
                'message' => "No se puede eliminar: esta ronda ya se usó en $usos jornada(s). Desactivala en vez de eliminarla."
            ], 409);
        }

        $rondaProgramada->delete();

        return response()->json([
            'message' => 'Ronda eliminada correctamente'
        ], 200);
    }
}
