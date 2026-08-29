<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre_usuario',
        'correo',
        'password',
        'activo',
        'hotel_id',
    ];

    //  debe_cambiar_password no va en fillable a proposito: lo pone el codigo,
    //  nunca una peticion

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo'                => 'boolean',
        'debe_cambiar_password' => 'boolean',
        'password'              => 'hashed',
    ];


    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'usuario_id', 'rol_id');
    }


    public function cambiosPassword(): HasMany
    {
        return $this->hasMany(CambioPassword::class, 'usuario_id');
    }


    //  El ultimo, para mostrar quien se la puso sin traer todo el historial
    public function ultimoCambioPassword(): HasOne
    {
        return $this->hasOne(CambioPassword::class, 'usuario_id')->latestOfMany();
    }


    //  Solo lo usan los usuarios con rol hotel
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }


    //  Los permisos se suman: basta con tener uno de los roles preguntados.
    //  Se compara contra el nombre del rol, nunca contra el id.
    public function tieneRol(string ...$nombres): bool
    {
        return $this->roles->whereIn('nombre', $nombres)->isNotEmpty();
    }


    public function esMaster(): bool
    {
        return $this->tieneRol(Rol::MASTER);
    }


    public function esAdministrador(): bool
    {
        return $this->tieneRol(Rol::ADMINISTRADOR);
    }


    //  Los nombres de sus roles, en orden. Son identificadores: para pantalla
    //  se pasan por Rol::etiquetaDe().
    public function nombresDeRoles(): array
    {
        return $this->roles->sortBy('id')->pluck('nombre')->all();
    }


    public function etiquetasDeRoles(): array
    {
        return $this->roles->sortBy('id')
            ->map(function ($rol) {
                return $rol->etiqueta;
            })->all();
    }
}
