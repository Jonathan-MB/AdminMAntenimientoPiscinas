<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    //  Los estados se comparan por nombre, nunca por posicion
    public const POR_HACER          = 'por_hacer';
    public const POR_FACTURAR       = 'por_facturar';
    public const POR_COBRAR         = 'por_cobrar';
    public const COBRADO            = 'cobrado';
    public const VISITA_REALIZADA   = 'visita_realizada';
    public const GARANTIA_REALIZADA = 'garantia_realizada';

    protected $fillable = [
        'titulo',
        'observacion',
        'estado',
        'cliente',
        'direccion',
        'usuario_id',
    ];

    //  No es una columna: la calcula la consulta del historial a partir del
    //  movimiento que cerro el ticket. El cast la deja como fecha igual.
    protected $casts = [
        'cerrado_en' => 'datetime',
    ];


    //  El orden es el del flujo de trabajo, no alfabetico
    public static function estados(): array
    {
        return [
            self::POR_HACER          => 'Por hacer',
            self::POR_FACTURAR       => 'Por facturar',
            self::POR_COBRAR         => 'Reparado y por cobrar',
            self::COBRADO            => 'Cobrado',
            self::VISITA_REALIZADA   => 'Visita realizada',
            self::GARANTIA_REALIZADA => 'Garantía realizada',
        ];
    }


    //  Los que siguen en el tablero, o sea los que aun piden trabajo
    public static function estadosAbiertos(): array
    {
        return [self::POR_HACER, self::POR_FACTURAR, self::POR_COBRAR];
    }


    //  Los que dan por terminada la reparacion. Salen del tablero y pasan al
    //  historial: cobrado cierra por pago, y los otros dos sin cobrar, porque
    //  una visita o una garantia no se facturan.
    public static function estadosCerrados(): array
    {
        return [self::COBRADO, self::VISITA_REALIZADA, self::GARANTIA_REALIZADA];
    }


    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }


    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoTicket::class, 'ticket_id');
    }


    public function fotos(): HasMany
    {
        return $this->hasMany(FotoTicket::class, 'ticket_id');
    }


    public function edicionesObservacion(): HasMany
    {
        return $this->hasMany(EdicionObservacionTicket::class, 'ticket_id');
    }


    public function getEtiquetaEstadoAttribute(): string
    {
        return self::estados()[$this->estado] ?? $this->estado;
    }
}
