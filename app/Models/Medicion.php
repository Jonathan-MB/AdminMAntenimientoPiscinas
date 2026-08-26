<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicion extends Model
{
    use HasFactory;

    //  Laravel pluralizaria "Medicion" como "medicions"
    protected $table = 'mediciones';

    protected $fillable = [
        'cl_libre',
        'cl_total',
        'cl_combinado',
        'ph',
        'alcalinidad',
        'dureza_calcio',
        'acido_cianurico',
        'retrolavado',
        'observacion',
        'ronda_id',
        'piscina_id',
    ];

    protected $casts = [
        'cl_libre'        => 'decimal:2',
        'cl_total'        => 'decimal:2',
        'cl_combinado'    => 'decimal:2',
        'ph'              => 'decimal:2',
        'alcalinidad'     => 'decimal:2',
        'dureza_calcio'   => 'decimal:2',
        'acido_cianurico' => 'decimal:2',
        'retrolavado'     => 'boolean',
    ];


    public function ronda(): BelongsTo
    {
        return $this->belongsTo(Ronda::class, 'ronda_id');
    }


    public function piscina(): BelongsTo
    {
        return $this->belongsTo(Piscina::class, 'piscina_id');
    }


    public function dosis(): HasMany
    {
        return $this->hasMany(Dosis::class, 'medicion_id');
    }
}
