<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetroAgua extends Model
{
    use HasFactory;

    protected $table = 'metros_agua';

    protected $fillable = [
        'nombre',
        'orden',
        'activo',
        'hotel_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];


    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }


    public function lecturas(): HasMany
    {
        return $this->hasMany(LecturaMetro::class, 'metro_agua_id');
    }
}
