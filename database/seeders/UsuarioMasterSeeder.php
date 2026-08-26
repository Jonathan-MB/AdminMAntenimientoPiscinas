<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsuarioMasterSeeder extends Seeder
{
    public function run(): void
    {
        $rol = Rol::where('nombre', Rol::MASTER)->first();

        if (! $rol) {
            $this->command->error('No existe el rol master. Corre primero RolSeeder.');

            return;
        }

        $nombreUsuario = env('MASTER_USUARIO', 'master');

        if (Usuario::where('nombre_usuario', $nombreUsuario)->exists()) {
            $this->command->info("El usuario master '$nombreUsuario' ya existe. No se toca.");

            return;
        }

        //  La contrasena nunca va escrita en el codigo: sale del .env o se genera
        $password = env('MASTER_PASSWORD');
        $generada = false;

        if (empty($password)) {
            $password = Str::random(16);
            $generada = true;
        }

        Usuario::create([
            'nombre_usuario' => $nombreUsuario,
            'correo'         => env('MASTER_CORREO', 'master@aqualive.test'),
            'password'       => $password,
            'activo'         => true,
            'rol_id'         => $rol->id,
        ]);

        $this->command->info("Usuario master creado: $nombreUsuario");

        if ($generada) {
            $this->command->warn("Contrasena generada: $password");
            $this->command->warn('Anotala ahora. No vuelve a mostrarse. Para fijarla, usa MASTER_PASSWORD en el .env.');
        }
    }
}
