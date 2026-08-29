<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicion extends Model
{
    use HasFactory;

    //  Los niveles se comparan por nombre, nunca por posicion
    public const NIVEL_ALTO   = 'alto';
    public const NIVEL_NORMAL = 'normal';
    public const NIVEL_BAJO   = 'bajo';

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
        'nivel_agua',
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


    public static function niveles(): array
    {
        return [
            self::NIVEL_NORMAL => 'Normal',
            self::NIVEL_ALTO   => 'Alto',
            self::NIVEL_BAJO   => 'Bajo',
        ];
    }


    public function ronda(): BelongsTo
    {
        return $this->belongsTo(Ronda::class, 'ronda_id');
    }


    public function piscina(): BelongsTo
    {
        return $this->belongsTo(Piscina::class, 'piscina_id');
    }


    //  Quien la registro. No va en fillable: lo pone el controlador.
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }


    public function dosis(): HasMany
    {
        return $this->hasMany(Dosis::class, 'medicion_id');
    }
}
