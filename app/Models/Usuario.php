<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'rol_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo'   => 'boolean',
        'password' => 'hashed',
    ];


    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }


    //  Compara contra el nombre del rol, no contra el id
    public function tieneRol(string ...$nombres): bool
    {
        return $this->rol !== null && in_array($this->rol->nombre, $nombres, true);
    }


    public function esMaster(): bool
    {
        return $this->tieneRol(Rol::MASTER);
    }


    public function esAdministrador(): bool
    {
        return $this->tieneRol(Rol::ADMINISTRADOR);
    }
}
