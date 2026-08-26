<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ronda extends Model
{
    use HasFactory;

    protected $fillable = [
        'hora',
        'observacion',
        'jornada_id',
        'ronda_programada_id',
    ];


    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'jornada_id');
    }


    public function rondaProgramada(): BelongsTo
    {
        return $this->belongsTo(RondaProgramada::class, 'ronda_programada_id');
    }


    public function mediciones(): HasMany
    {
        return $this->hasMany(Medicion::class, 'ronda_id');
    }
}
