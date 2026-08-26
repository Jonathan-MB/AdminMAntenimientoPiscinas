<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $fillable = [
        'nombre',
        'descripcion',
    ];


    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }
}
