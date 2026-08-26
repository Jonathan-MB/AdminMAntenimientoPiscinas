<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ronda extends Model
{
    use HasFactory;

    //  Los turnos se comparan por nombre, nunca por id
    public const MANANA = 'manana';
    public const TARDE  = 'tarde';

    protected $fillable = [
        'turno',
        'hora',
        'observacion',
        'jornada_id',
    ];


    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'jornada_id');
    }


    public function mediciones(): HasMany
    {
        return $this->hasMany(Medicion::class, 'ronda_id');
    }
}
