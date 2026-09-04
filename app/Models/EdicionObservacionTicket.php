<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EdicionObservacionTicket extends Model
{
    use HasFactory;

    //  Laravel pluralizaria la clase de otra forma
    protected $table = 'ediciones_observacion_ticket';

    protected $fillable = [
        'texto_anterior',
        'texto_nuevo',
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
