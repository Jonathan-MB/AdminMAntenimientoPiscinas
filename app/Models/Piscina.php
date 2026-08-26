<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Piscina extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
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


    public function mediciones(): HasMany
    {
        return $this->hasMany(Medicion::class, 'piscina_id');
    }
}
