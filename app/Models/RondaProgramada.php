<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RondaProgramada extends Model
{
    use HasFactory;

    protected $table = 'rondas_programadas';

    protected $fillable = [
        'nombre',
        'hora',
        'orden',
        'activa',
        'hotel_id',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];


    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }


    public function rondas(): HasMany
    {
        return $this->hasMany(Ronda::class, 'ronda_programada_id');
    }
}
