<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jornada extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'lectura_metro_agua',
        'entregada',
        'hotel_id',
        'usuario_id',
    ];

    protected $casts = [
        'fecha'              => 'date',
        'lectura_metro_agua' => 'decimal:2',
        'entregada'          => 'boolean',
    ];


    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }


    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }


    public function rondas(): HasMany
    {
        return $this->hasMany(Ronda::class, 'jornada_id');
    }


    public function tareasRealizadas(): HasMany
    {
        return $this->hasMany(TareaRealizada::class, 'jornada_id');
    }
}
