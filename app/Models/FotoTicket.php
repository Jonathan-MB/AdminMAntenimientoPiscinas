<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoTicket extends Model
{
    use HasFactory;

    //  Cuantas caben por ticket y cuanto pesa cada una como maximo
    public const MAXIMO_POR_TICKET = 6;
    public const MAXIMO_KB         = 5120;

    protected $table = 'fotos_ticket';

    protected $fillable = [
        'ruta',
        'nombre_original',
        'ticket_id',
        'usuario_id',
    ];


    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }


    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
