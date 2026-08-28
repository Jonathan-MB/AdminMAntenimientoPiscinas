<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LecturaMetro extends Model
{
    use HasFactory;

    protected $table = 'lecturas_metro';

    protected $fillable = [
        'lectura',
        'jornada_id',
        'metro_agua_id',
    ];

    protected $casts = [
        'lectura' => 'decimal:2',
    ];


    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'jornada_id');
    }


    public function metroAgua(): BelongsTo
    {
        return $this->belongsTo(MetroAgua::class, 'metro_agua_id');
    }
}
