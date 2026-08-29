<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFotoTicketRequest;
use App\Models\FotoTicket;
use App\Models\Rol;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FotoTicketController extends Controller
{
    public function store(StoreFotoTicketRequest $request, Ticket $ticket)
    {
        $subidas = $request->file('fotos');
        $tiene   = $ticket->fotos()->count();

        if ($tiene + count($subidas) > FotoTicket::MAXIMO_POR_TICKET) {
            $caben = FotoTicket::MAXIMO_POR_TICKET - $tiene;

            return back()->withErrors([
                'fotos' => $caben > 0
                    ? 'Este ticket ya tiene ' . $tiene . ' fotos, solo caben ' . $caben . ' más'
                    : 'Este ticket ya tiene las ' . FotoTicket::MAXIMO_POR_TICKET . ' fotos permitidas',
            ]);
        }

        //  Los archivos se escriben fuera de la transaccion: si algo falla
        //  despues, el archivo huerfano no rompe nada; una fila sin archivo si.
        $guardadas = [];

        foreach ($subidas as $subida) {
            $nombre = Str::uuid() . '.' . strtolower($subida->getClientOriginalExtension());

            $subida->storeAs('tickets/' . $ticket->id, $nombre, 'local');

            $guardadas[] = [
                'ruta'            => 'tickets/' . $ticket->id . '/' . $nombre,
                'nombre_original' => Str::limit($subida->getClientOriginalName(), 120, ''),
                'ticket_id'       => $ticket->id,
                'usuario_id'      => Auth::id(),
            ];
        }

        DB::transaction(function () use ($guardadas) {
            foreach ($guardadas as $foto) {
                FotoTicket::create($foto);
            }
        });

        $cuantas = count($guardadas);

        return back()->with('mensajeCreado', $cuantas === 1 ? 'Foto agregada' : $cuantas . ' fotos agregadas');
    }



    //  Borra el jefe, el master, o quien la subio
    public function destroy(Request $request, FotoTicket $foto)
    {
        $usuario = Auth::user();

        if (! $usuario->tieneRol(Rol::MASTER, Rol::JEFE) && $foto->usuario_id !== $usuario->id) {
            return response()->json([
                'message' => 'Solo puedes borrar las fotos que tú subiste'
            ], 403);
        }

        Storage::disk('local')->delete($foto->ruta);
        $foto->delete();

        return response()->json([
            'message' => 'Foto eliminada'
        ], 200);
    }



    //  Sirve el archivo. No esta en public a proposito: asi pasa por el
    //  middleware de rol y nadie la ve con solo tener la direccion.
    public function ver(Request $request, FotoTicket $foto)
    {
        if (! Storage::disk('local')->exists($foto->ruta)) {
            abort(404);
        }

        return Storage::disk('local')->response($foto->ruta, $foto->nombre_original, [
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }
}
