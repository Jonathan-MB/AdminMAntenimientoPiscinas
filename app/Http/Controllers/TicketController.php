<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditarObservacionTicketRequest;
use App\Http\Requests\MoverTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Models\EdicionObservacionTicket;
use App\Models\MovimientoTicket;
use App\Models\Rol;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    //  El tablero: los tickets que siguen abiertos, agrupados por estado
    public function index(Request $request)
    {
        $tickets = Ticket::with('usuario')
            ->withCount('fotos')
            ->whereIn('estado', Ticket::estadosAbiertos())
            ->orderBy('created_at')
            ->get()
            ->groupBy('estado');

        return view('reparaciones', compact('tickets'));
    }



    public function store(StoreTicketRequest $request)
    {
        $datos = $request->validated();
        $datos['estado']     = Ticket::POR_HACER;
        $datos['usuario_id'] = Auth::id();

        DB::transaction(function () use ($datos) {
            $ticket = Ticket::create($datos);

            //  El primer movimiento es la creacion: sin estado anterior
            MovimientoTicket::create([
                'estado_anterior' => null,
                'estado_nuevo'    => $ticket->estado,
                'ticket_id'       => $ticket->id,
                'usuario_id'      => Auth::id(),
            ]);
        });

        return redirect()->route('reparaciones.index')->with('mensajeCreado', 'Ticket creado correctamente');
    }



    public function show(Request $request, Ticket $ticket)
    {
        $ticket->load(['usuario', 'fotos', 'movimientos' => function ($consulta) {
            $consulta->with('usuario')->orderByDesc('created_at')->orderByDesc('id');
        }, 'edicionesObservacion' => function ($consulta) {
            $consulta->with('usuario')->orderByDesc('created_at')->orderByDesc('id');
        }]);

        return view('ticket', compact('ticket'));
    }



    //  Mover de estado. El reparador puede moverlos todos; queda el rastro.
    public function update(MoverTicketRequest $request, Ticket $ticket)
    {
        $nuevo = $request->validated()['estado'];

        if ($nuevo === $ticket->estado) {
            return response()->json([
                'message' => 'El ticket ya estaba en ese estado'
            ], 422);
        }

        $anterior = $ticket->estado;

        DB::transaction(function () use ($ticket, $anterior, $nuevo) {
            $ticket->estado = $nuevo;
            $ticket->save();

            MovimientoTicket::create([
                'estado_anterior' => $anterior,
                'estado_nuevo'    => $nuevo,
                'ticket_id'       => $ticket->id,
                'usuario_id'      => Auth::id(),
            ]);
        });

        return response()->json([
            'message' => 'Movido a ' . Ticket::estados()[$nuevo],
            'estado'  => $nuevo,
        ], 200);
    }



    //  Solo el jefe borra tickets. El master tambien, porque ve todo.
    public function destroy(Request $request, Ticket $ticket)
    {
        $usuario = Auth::user();

        if (! $usuario->tieneRol(Rol::MASTER, Rol::JEFE)) {
            return response()->json([
                'message' => 'Solo el jefe puede eliminar tickets'
            ], 403);
        }

        //  La base borra las filas en cascada, pero los archivos hay que
        //  quitarlos a mano o quedan ocupando el servidor para siempre
        Storage::disk('local')->deleteDirectory('tickets/' . $ticket->id);

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket eliminado correctamente'
        ], 200);
    }



    //  La observacion se corrige mientras avanza la reparacion. Cada edicion
    //  guarda lo que decia antes: sin eso, corregirla borraria lo anterior.
    public function observacion(EditarObservacionTicketRequest $request, Ticket $ticket)
    {
        $antes    = $ticket->observacion;
        $despues  = $request->validated()['observacion'] ?? null;

        if ($antes === $despues) {
            return response()->json([
                'message' => 'La observación no cambió'
            ], 422);
        }

        DB::transaction(function () use ($ticket, $antes, $despues) {
            $ticket->observacion = $despues;
            $ticket->save();

            EdicionObservacionTicket::create([
                'texto_anterior' => $antes,
                'texto_nuevo'    => $despues,
                'ticket_id'      => $ticket->id,
                'usuario_id'     => Auth::id(),
            ]);
        });

        return response()->json([
            'message'     => 'Observación guardada',
            'observacion' => $despues,
            'autor'       => Auth::user()->nombre_usuario,
            'cuando'      => now()->format('d/m/Y H:i'),
        ], 200);
    }



    //  Los cobrados, con filtro por cliente y por fecha
    public function historial(Request $request)
    {
        $cliente = $request->query('cliente');
        $desde   = $request->query('desde');
        $hasta   = $request->query('hasta');

        $consulta = Ticket::with('usuario')
            ->where('estado', Ticket::COBRADO);

        if ($cliente) {
            $consulta->where('cliente', 'like', '%' . $cliente . '%');
        }

        if ($desde) {
            $consulta->whereDate('updated_at', '>=', $desde);
        }

        if ($hasta) {
            $consulta->whereDate('updated_at', '<=', $hasta);
        }

        $total = (clone $consulta)->count();

        $tickets = $consulta->orderByDesc('updated_at')->limit(50)->get();

        return view('historial-reparaciones', compact('tickets', 'total', 'cliente', 'desde', 'hasta'));
    }


    //  Lo que consulta el sondeo cada 15 segundos. El sello cambia si entra un
    //  ticket, si alguno se mueve o si se borra: con eso basta para avisar.
    public function resumen(Request $request)
    {
        $abiertos = Ticket::whereIn('estado', Ticket::estadosAbiertos())
            ->orderBy('id')
            ->get(['id', 'estado']);

        $conteos = [];

        foreach (Ticket::estadosAbiertos() as $estado) {
            $conteos[$estado] = $abiertos->where('estado', $estado)->count();
        }

        $huella = $abiertos->map(function ($ticket) {
            return $ticket->id . ':' . $ticket->estado;
        })->implode(',');

        return response()->json([
            'conteos'  => $conteos,
            'abiertos' => $abiertos->count(),
            'sello'    => md5($huella),
        ], 200)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
