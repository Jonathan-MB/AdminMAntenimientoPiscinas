<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'unidad',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];


    public function dosis(): HasMany
    {
        return $this->hasMany(Dosis::class, 'producto_id');
    }
}
