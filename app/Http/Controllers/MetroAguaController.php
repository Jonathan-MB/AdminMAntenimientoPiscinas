<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMetroAguaRequest;
use App\Http\Requests\UpdateMetroAguaRequest;
use App\Models\Hotel;
use App\Models\MetroAgua;
use Illuminate\Http\Request;

class MetroAguaController extends Controller
{
    public function store(StoreMetroAguaRequest $request, Hotel $hotel)
    {
        $datos = $request->validated();

        //  Si no mandan orden, va al final
        if (! isset($datos['orden'])) {
            $datos['orden'] = (int) $hotel->metrosAgua()->max('orden') + 1;
        }

        $datos['hotel_id'] = $hotel->id;

        MetroAgua::create($datos);

        return redirect()->back()->with('mensajeCreado', 'Metro de agua creado correctamente');
    }



    public function update(UpdateMetroAguaRequest $request, MetroAgua $metroAgua)
    {
        $data = $request->validated();

        // PATCH sin data
        if (empty($data)) {
            return response()->json([
                'message' => 'Sin datos'
            ], 422);
        }

        // Cargar datos sin guardar
        $metroAgua->fill($data);

        // No hubo cambios
        if (! $metroAgua->isDirty()) {
            return response()->json([
                'message' => 'No se detectaron cambios'
            ], 422);
        }

        $metroAgua->save();

        return response()->json([
            'message' => 'Actualizado Correctamente',
            'data'    => $metroAgua->fresh()
        ], 200);
    }



    public function destroy(Request $request, MetroAgua $metroAgua)
    {
        //  Si ya tiene lecturas, borrarlo dejaria huecos en el historico
        $lecturas = $metroAgua->lecturas()->count();

        if ($lecturas > 0) {
            return response()->json([
                'message' => "No se puede eliminar: este metro ya tiene $lecturas lectura(s) registrada(s). Desactivalo en vez de eliminarlo."
            ], 409);
        }

        $metroAgua->delete();

        return response()->json([
            'message' => 'Metro de agua eliminado correctamente'
        ], 200);
    }
}
