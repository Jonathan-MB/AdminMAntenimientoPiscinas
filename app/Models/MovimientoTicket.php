<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoTicket extends Model
{
    use HasFactory;

    protected $table = 'movimientos_ticket';

    protected $fillable = [
        'estado_anterior',
        'estado_nuevo',
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


    public function getEtiquetaAnteriorAttribute(): ?string
    {
        return $this->estado_anterior ? (Ticket::estados()[$this->estado_anterior] ?? $this->estado_anterior) : null;
    }


    public function getEtiquetaNuevaAttribute(): string
    {
        return Ticket::estados()[$this->estado_nuevo] ?? $this->estado_nuevo;
    }
}
