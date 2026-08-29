<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            Rol::MASTER        => 'Control total. No puede ser eliminado por nadie.',
            Rol::ADMINISTRADOR => 'Crea usuarios y asigna roles. No puede eliminar administradores.',
            Rol::COLABORADOR   => 'Ingresa la informacion de mantenimiento.',
            Rol::HOTEL         => 'Consulta su informacion y la de sus piscinas. Solo lectura.',
            Rol::JEFE          => 'Ve las reparaciones, crea tickets y es el unico que puede borrarlos.',
            Rol::REPARACION    => 'Ve las reparaciones y crea tickets.',
        ];

        foreach ($roles as $nombre => $descripcion) {
            Rol::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion]
            );
        }
    }
}
