<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dosis extends Model
{
    use HasFactory;

    //  "dosis" ya es plural: Laravel buscaria "dosis" igual, pero lo dejamos explicito
    protected $table = 'dosis';

    protected $fillable = [
        'cantidad',
        'medicion_id',
        'producto_id',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
    ];


    public function medicion(): BelongsTo
    {
        return $this->belongsTo(Medicion::class, 'medicion_id');
    }


    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
