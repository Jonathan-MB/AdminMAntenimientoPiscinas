<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoverTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Hotel;
use App\Models\MovimientoTicket;
use App\Models\Rol;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    //  El tablero: los tickets que siguen abiertos, agrupados por estado
    public function index(Request $request)
    {
        $tickets = Ticket::with('hotel', 'usuario')
            ->whereIn('estado', Ticket::estadosAbiertos())
            ->orderBy('created_at')
            ->get()
            ->groupBy('estado');

        $hoteles = Hotel::where('activo', true)->orderBy('nombre')->get();

        return view('reparaciones', compact('tickets', 'hoteles'));
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
        $ticket->load(['hotel', 'usuario', 'movimientos' => function ($consulta) {
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

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket eliminado correctamente'
        ], 200);
    }



    //  Los cobrados, con filtro por hotel y por fecha
    public function historial(Request $request)
    {
        $hotelId = $request->query('hotel');
        $desde   = $request->query('desde');
        $hasta   = $request->query('hasta');

        $consulta = Ticket::with('hotel', 'usuario')
            ->where('estado', Ticket::COBRADO);

        if ($hotelId) {
            $consulta->where('hotel_id', $hotelId);
        }

        if ($desde) {
            $consulta->whereDate('updated_at', '>=', $desde);
        }

        if ($hasta) {
            $consulta->whereDate('updated_at', '<=', $hasta);
        }

        $total = (clone $consulta)->count();

        $tickets = $consulta->orderByDesc('updated_at')->limit(50)->get();
        $hoteles = Hotel::orderBy('nombre')->get();

        return view('historial-reparaciones', compact('tickets', 'hoteles', 'total', 'hotelId', 'desde', 'hasta'));
    }
}
