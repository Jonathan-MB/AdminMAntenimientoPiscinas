<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicionRequest;
use App\Models\Cambio;
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
            $aviso = 'Esta jornada ya no se puede editar. Pide a un administrador que la corrija.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $aviso], 403);
            }

            return redirect()->back()->with('error', $aviso);
        }

        $datos = $request->validated();
        $dosis = $datos['dosis'] ?? [];
        unset($datos['dosis'], $datos['llavesDosis']);

        $donde = $piscina->nombre . ' · ' . $rondaProgramada->nombre;

        DB::transaction(function () use ($jornada, $rondaProgramada, $piscina, $datos, $dosis, $donde) {

            //  La ronda se crea la primera vez que se guarda una piscina
            $ronda = Ronda::firstOrCreate(
                ['jornada_id' => $jornada->id, 'ronda_programada_id' => $rondaProgramada->id],
                ['hora' => $rondaProgramada->hora]
            );

            $medicion = Medicion::firstOrNew(['ronda_id' => $ronda->id, 'piscina_id' => $piscina->id]);

            //  Lo que habia antes, para poder comparar
            $antes = $medicion->exists ? $medicion->getOriginal() : [];
            $dosisAntes = $medicion->exists
                ? $medicion->dosis()->with('producto')->get()->pluck('cantidad', 'producto.nombre')->all()
                : [];

            $medicion->fill($datos);
            $medicion->save();

            //  Se rehacen las dosis: lo que llega en blanco es "no se aplicó"
            $medicion->dosis()->delete();

            $dosisDespues = [];

            foreach ($dosis as $productoId => $cantidad) {
                if ($cantidad === null || $cantidad === '' || (float) $cantidad <= 0) {
                    continue;
                }

                $nueva = Dosis::create([
                    'cantidad'    => $cantidad,
                    'medicion_id' => $medicion->id,
                    'producto_id' => $productoId,
                ]);

                $dosisDespues[$nueva->producto->nombre] = $nueva->cantidad;
            }

            $this->anotarCambios($jornada, $donde, $antes, $medicion->getAttributes(), $dosisAntes, $dosisDespues);
        });

        //  El guardado automatico pide JSON; el formulario sin JavaScript, no
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Guardado',
                'hora'    => now()->format('H:i'),
            ], 200);
        }

        return redirect()->route('registro.jornada', $jornada)
            ->with('mensajeCreado', $piscina->nombre . ' guardada');
    }



    //  Anota que cambio, solo si el campo YA tenia valor. Llenar un campo
    //  vacio por primera vez no es una correccion.
    private function anotarCambios($jornada, string $donde, array $antes, array $despues, array $dosisAntes, array $dosisDespues): void
    {
        $etiquetas = [
            'cl_libre'        => 'Cloro libre',
            'cl_total'        => 'Cloro total',
            'cl_combinado'    => 'Combinado',
            'ph'              => 'pH',
            'alcalinidad'     => 'Alcalinidad',
            'dureza_calcio'   => 'Dureza de calcio',
            'acido_cianurico' => 'Ácido cianúrico',
            'nivel_agua'      => 'Nivel del agua',
            'retrolavado'     => 'Retrolavado',
            'observacion'     => 'Observación',
        ];

        foreach ($etiquetas as $campo => $etiqueta) {
            $this->anotarUno($jornada, $donde, $etiqueta, $antes[$campo] ?? null, $despues[$campo] ?? null);
        }

        //  Los quimicos: se comparan por nombre de producto
        foreach (array_unique(array_merge(array_keys($dosisAntes), array_keys($dosisDespues))) as $producto) {
            $this->anotarUno($jornada, $donde, $producto, $dosisAntes[$producto] ?? null, $dosisDespues[$producto] ?? null);
        }
    }



    private function anotarUno($jornada, string $donde, string $campo, $antes, $despues): void
    {
        //  Sin valor previo no hay correccion que anotar
        if ($antes === null || $antes === '') {
            return;
        }

        //  Se comparan como texto: 7.40 y "7.40" son el mismo valor
        if ((string) $antes === (string) $despues) {
            return;
        }

        Cambio::create([
            'donde'          => $donde,
            'campo'          => $campo,
            'valor_anterior' => (string) $antes,
            'valor_nuevo'    => $despues === null || $despues === '' ? null : (string) $despues,
            'jornada_id'     => $jornada->id,
            'usuario_id'     => Auth::id(),
        ]);
    }



    //  Que la ronda y la piscina sean de verdad del hotel de esta jornada
    private function verificarPertenencia(Jornada $jornada, RondaProgramada $rondaProgramada, Piscina $piscina): void
    {
        if ($rondaProgramada->hotel_id !== $jornada->hotel_id || $piscina->hotel_id !== $jornada->hotel_id) {
            abort(404);
        }
    }
}
