<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cambio extends Model
{
    use HasFactory;

    protected $fillable = [
        'donde',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'jornada_id',
        'usuario_id',
    ];


    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'jornada_id');
    }


    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
