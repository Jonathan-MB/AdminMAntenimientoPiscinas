<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    use HasFactory;

    //  Laravel pluralizaria "Rol" como "rols"
    protected $table = 'roles';

    //  Los nombres de rol son la llave de la autorizacion: se buscan por nombre, nunca por id
    public const MASTER        = 'master';
    public const ADMINISTRADOR = 'administrador';
    public const COLABORADOR   = 'colaborador';
    public const HOTEL         = 'hotel';
    public const JEFE          = 'jefe';
    public const REPARACION    = 'reparacion';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];


    //  El identificador va sin tilde porque se compara en codigo; lo que lee
    //  una persona si la lleva.
    public static function etiquetas(): array
    {
        return [
            self::MASTER        => 'Master',
            self::ADMINISTRADOR => 'Administrador',
            self::COLABORADOR   => 'Colaborador',
            self::HOTEL         => 'Hotel',
            self::JEFE          => 'Jefe',
            self::REPARACION    => 'Reparación',
        ];
    }


    public static function etiquetaDe(string $nombre): string
    {
        return self::etiquetas()[$nombre] ?? ucfirst($nombre);
    }


    public function getEtiquetaAttribute(): string
    {
        return self::etiquetaDe($this->nombre);
    }


    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'rol_usuario', 'rol_id', 'usuario_id');
    }
}
