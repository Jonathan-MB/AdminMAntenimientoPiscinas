<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CambioPassword extends Model
{
    use HasFactory;

    protected $table = 'cambios_password';

    //  Es un registro de hechos: se escribe una vez y no se toca
    public const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'autor_id',
    ];


    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }


    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'autor_id');
    }


    //  Deja la linea escrita. Si el autor es el mismo usuario, se la cambio el.
    public static function anotar(int $usuarioId, ?int $autorId): void
    {
        self::create([
            'usuario_id' => $usuarioId,
            'autor_id'   => $autorId,
        ]);
    }


    public function getFuePropioAttribute(): bool
    {
        return $this->autor_id === $this->usuario_id;
    }
}
