<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarea extends Model
{
    use HasFactory;

    protected $fillable = [
        'descripcion',
        'orden',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];


    public function realizadas(): HasMany
    {
        return $this->hasMany(TareaRealizada::class, 'tarea_id');
    }
}
