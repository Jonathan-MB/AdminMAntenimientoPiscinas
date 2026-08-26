<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    use HasFactory;

    //  Laravel pluralizaria "Hotel" como "hotels"
    protected $table = 'hoteles';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'contacto',
        'hora_ronda_manana',
        'hora_ronda_tarde',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];


    public function piscinas(): HasMany
    {
        return $this->hasMany(Piscina::class, 'hotel_id');
    }


    public function jornadas(): HasMany
    {
        return $this->hasMany(Jornada::class, 'hotel_id');
    }


    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'hotel_id');
    }
}
