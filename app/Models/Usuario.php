<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo'   => 'boolean',
        'password' => 'hashed',
    ];


    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'usuario_id', 'rol_id');
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


    //  Los nombres de sus roles, en orden, para mostrarlos
    public function nombresDeRoles(): array
    {
        return $this->roles->sortBy('id')->pluck('nombre')->all();
    }
}
