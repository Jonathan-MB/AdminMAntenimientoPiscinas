<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicionRequest;
use App\Models\Dosis;
use App\Models\Jornada;
use App\Models\Medicion;
use App\Models\Piscina;
use App\Models\Producto;
use App\Models\Ronda;
use App\Models\RondaProgramada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicionController extends Controller
{
    //  Pantalla de una sola piscina dentro de una ronda
    public function edit(Request $request, Jornada $jornada, RondaProgramada $rondaProgramada, Piscina $piscina)
    {
        $this->verificarPertenencia($jornada, $rondaProgramada, $piscina);

        $editable  = $jornada->puedeEditarla(Auth::user());
        $productos = Producto::where('activo', true)->orderBy('orden')->get();

        $ronda = $jornada->rondas()->where('ronda_programada_id', $rondaProgramada->id)->first();

        $medicion = $ronda
            ? $ronda->mediciones()->with('dosis')->where('piscina_id', $piscina->id)->first()
            : null;

        //  Cantidades ya aplicadas, indexadas por producto
        $cantidades = $medicion
            ? $medicion->dosis->pluck('cantidad', 'producto_id')->all()
            : [];

        //  Las piscinas activas del hotel, en orden, para moverse entre ellas
        $piscinas = $jornada->hotel->piscinas()
            ->where('activa', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $posicion  = $piscinas->search(fn ($p) => $p->id === $piscina->id);
        $anterior  = $posicion > 0 ? $piscinas[$posicion - 1] : null;
        $siguiente = $posicion < $piscinas->count() - 1 ? $piscinas[$posicion + 1] : null;

        return view('medicion', compact(
            'jornada', 'rondaProgramada', 'piscina', 'medicion', 'productos',
            'cantidades', 'ronda', 'editable', 'piscinas', 'posicion', 'anterior', 'siguiente'
        ));
    }



    public function store(StoreMedicionRequest $request, Jornada $jornada, RondaProgramada $rondaProgramada, Piscina $piscina)
    {
        $this->verificarPertenencia($jornada, $rondaProgramada, $piscina);

        if (! $jornada->puedeEditarla(Auth::user())) {
            return redirect()->back()
                ->with('error', 'Esta jornada ya no se puede editar. Pide a un administrador que la corrija.');
        }

        $datos = $request->validated();
        $dosis = $datos['dosis'] ?? [];
        unset($datos['dosis'], $datos['llavesDosis']);

        DB::transaction(function () use ($jornada, $rondaProgramada, $piscina, $datos, $dosis) {

            //  La ronda se crea la primera vez que se guarda una piscina
            $ronda = Ronda::firstOrCreate(
                ['jornada_id' => $jornada->id, 'ronda_programada_id' => $rondaProgramada->id],
                ['hora' => $rondaProgramada->hora]
            );

            $medicion = Medicion::updateOrCreate(
                ['ronda_id' => $ronda->id, 'piscina_id' => $piscina->id],
                $datos
            );

            //  Se rehacen las dosis: lo que llega en blanco es "no se aplicó"
            $medicion->dosis()->delete();

            foreach ($dosis as $productoId => $cantidad) {
                if ($cantidad === null || $cantidad === '' || (float) $cantidad <= 0) {
                    continue;
                }

                Dosis::create([
                    'cantidad'    => $cantidad,
                    'medicion_id' => $medicion->id,
                    'producto_id' => $productoId,
                ]);
            }
        });

        //  "Guardar y siguiente" manda a donde diga el boton
        if ($request->filled('siguiente')) {
            return redirect()->route('registro.medicion', [
                'jornada'         => $jornada,
                'rondaProgramada' => $rondaProgramada,
                'piscina'         => $request->input('siguiente'),
            ])->with('mensajeCreado', $piscina->nombre . ' guardada');
        }

        return redirect()->route('registro.jornada', $jornada)
            ->with('mensajeCreado', $piscina->nombre . ' guardada');
    }



    //  Que la ronda y la piscina sean de verdad del hotel de esta jornada
    private function verificarPertenencia(Jornada $jornada, RondaProgramada $rondaProgramada, Piscina $piscina): void
    {
        if ($rondaProgramada->hotel_id !== $jornada->hotel_id || $piscina->hotel_id !== $jornada->hotel_id) {
            abort(404);
        }
    }
}
