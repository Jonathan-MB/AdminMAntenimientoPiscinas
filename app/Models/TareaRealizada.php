<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TareaRealizada extends Model
{
    use HasFactory;

    protected $table = 'tareas_realizadas';

    protected $fillable = [
        'hecha',
        'marcada_en',
        'jornada_id',
        'tarea_id',
    ];

    protected $casts = [
        'hecha'      => 'boolean',
        'marcada_en' => 'datetime',
    ];


    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'jornada_id');
    }


    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }
}
