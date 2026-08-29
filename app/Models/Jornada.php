<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jornada extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'materiales_sacados',
        'hotel_id',
        'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];


    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }


    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }


    public function rondas(): HasMany
    {
        return $this->hasMany(Ronda::class, 'jornada_id');
    }


    public function tareasRealizadas(): HasMany
    {
        return $this->hasMany(TareaRealizada::class, 'jornada_id');
    }


    public function lecturasMetro(): HasMany
    {
        return $this->hasMany(LecturaMetro::class, 'jornada_id');
    }


    public function cambios(): HasMany
    {
        return $this->hasMany(Cambio::class, 'jornada_id');
    }


    //  La fecha se compara contra hoy en hora de Aruba, no la del servidor
    public function esDeHoy(): bool
    {
        return $this->fecha->isSameDay(Carbon::today());
    }


    //  Trabajo con esta jornada: la abrio, o registro alguna medicion en ella
    public function participo(Usuario $usuario): bool
    {
        if ($this->usuario_id === $usuario->id) {
            return true;
        }

        return Medicion::whereIn('ronda_id', $this->rondas()->select('id'))
            ->where('usuario_id', $usuario->id)
            ->exists();
    }


    //  Un colaborador solo ve lo suyo. La de hoy es la excepcion: dos pueden
    //  repartirse el dia, y el segundo tiene que poder entrar a la que abrio
    //  el primero.
    public function puedeVerla(Usuario $usuario): bool
    {
        if ($usuario->esMaster() || $usuario->esAdministrador()) {
            return true;
        }

        if ($this->esDeHoy()) {
            return true;
        }

        return $this->participo($usuario);
    }


    //  El colaborador solo corrige lo del mismo dia. Pasada la medianoche,
    //  la correccion la hace un administrador.
    public function puedeEditarla(Usuario $usuario): bool
    {
        if ($usuario->esMaster() || $usuario->esAdministrador()) {
            return true;
        }

        if (! $usuario->tieneRol(Rol::COLABORADOR)) {
            return false;
        }

        return $this->esDeHoy();
    }
}
