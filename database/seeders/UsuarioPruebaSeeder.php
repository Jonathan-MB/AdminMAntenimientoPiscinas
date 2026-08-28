<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuarioPruebaSeeder extends Seeder
{
    //  Cuentas para revisar como se ve la aplicacion con cada rol durante el
    //  desarrollo. NO se llama desde DatabaseSeeder: se corre a mano.
    //  Hay que borrarlas antes de produccion.
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('Este seeder no se corre en producción.');

            return;
        }

        $clave = env('PRUEBAS_PASSWORD', 'pruebas2026');
        $hotel = Hotel::first();

        $cuentas = [
            ['admin1',     Rol::ADMINISTRADOR, null],
            ['colab1',     Rol::COLABORADOR,   null],
            ['hotelaruba', Rol::HOTEL,         $hotel?->id],
        ];

        foreach ($cuentas as $cuenta) {
            list($nombre, $rolNombre, $hotelId) = $cuenta;

            $rol = Rol::where('nombre', $rolNombre)->first();

            if (! $rol) {
                continue;
            }

            $usuario = Usuario::firstOrNew(['nombre_usuario' => $nombre]);
            $usuario->correo   = $nombre . '@aqualive.test';
            $usuario->password = $clave;
            $usuario->activo   = true;
            $usuario->rol_id   = $rol->id;
            $usuario->hotel_id = $hotelId;
            $usuario->save();

            $this->command->info("Cuenta de prueba: $nombre ($rolNombre)");
        }

        $this->command->warn("Contraseña de las tres: $clave. Bórralas antes de producción.");
    }
}
