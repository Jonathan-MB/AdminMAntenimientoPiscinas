<?php

namespace Database\Seeders;

use App\Models\Dosis;
use App\Models\Hotel;
use App\Models\Jornada;
use App\Models\LecturaMetro;
use App\Models\Medicion;
use App\Models\Producto;
use App\Models\Ronda;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class JornadaDemoSeeder extends Seeder
{
    //  Datos de ejemplo para poder ver el diario funcionando mientras
    //  el formulario del colaborador todavia no existe.
    //  NO se llama desde DatabaseSeeder: se corre a mano y se puede borrar.
    public function run(): void
    {
        $hotel = Hotel::with('piscinas', 'rondasProgramadas', 'metrosAgua')->first();

        if (! $hotel) {
            $this->command->error('No hay hoteles. Corre primero HotelSeeder.');

            return;
        }

        $usuario = Usuario::whereHas('rol', function ($consulta) {
            $consulta->where('nombre', Rol::COLABORADOR);
        })->first() ?? Usuario::first();

        $productos = Producto::orderBy('orden')->get();

        $observaciones = [
            'Agua clara, sin novedad',
            'Se cepillaron los bordes del spa',
            'Nivel de agua bajo, se puso a llenar',
            'Filtro con presión alta, se hizo retrolavado',
            null,
        ];

        $creadas = 0;

        //  Los ultimos 18 dias, saltando algunos para que el calendario
        //  muestre dias con y sin registro
        for ($atras = 0; $atras < 18; $atras++) {
            if ($atras % 5 === 3) {
                continue;
            }

            $fecha = now()->subDays($atras)->toDateString();

            if (Jornada::where('hotel_id', $hotel->id)->whereDate('fecha', $fecha)->exists()) {
                continue;
            }

            $jornada = Jornada::create([
                'fecha'              => $fecha,
                'materiales_sacados' => 'Ácido muriático, tabletas de cloro',
                'hotel_id'           => $hotel->id,
                'usuario_id'         => $usuario->id,
            ]);

            foreach ($hotel->metrosAgua as $metro) {
                LecturaMetro::create([
                    'lectura'       => 145000 + $atras * 37.5,
                    'jornada_id'    => $jornada->id,
                    'metro_agua_id' => $metro->id,
                ]);
            }

            foreach ($hotel->rondasProgramadas as $programada) {
                $ronda = Ronda::create([
                    'hora'                => $programada->hora,
                    'observacion'         => $observaciones[array_rand($observaciones)],
                    'jornada_id'          => $jornada->id,
                    'ronda_programada_id' => $programada->id,
                ]);

                foreach ($hotel->piscinas as $piscina) {
                    $medicion = Medicion::create([
                        'cl_libre'        => round(mt_rand(80, 320) / 100, 2),
                        'cl_total'        => round(mt_rand(100, 360) / 100, 2),
                        'cl_combinado'    => round(mt_rand(0, 40) / 100, 2),
                        'ph'              => round(mt_rand(700, 800) / 100, 2),
                        'alcalinidad'     => mt_rand(70, 130),
                        'dureza_calcio'   => mt_rand(180, 400),
                        'acido_cianurico' => mt_rand(20, 60),
                        'retrolavado'     => mt_rand(0, 6) === 0,
                        'nivel_agua'      => ['normal', 'normal', 'normal', 'alto', 'bajo'][mt_rand(0, 4)],
                        'ronda_id'        => $ronda->id,
                        'piscina_id'      => $piscina->id,
                    ]);

                    //  Uno o dos quimicos aplicados, no siempre
                    foreach ($productos->random(mt_rand(0, 2)) as $producto) {
                        Dosis::create([
                            'cantidad'    => round(mt_rand(25, 300) / 100, 2),
                            'medicion_id' => $medicion->id,
                            'producto_id' => $producto->id,
                        ]);
                    }
                }
            }

            $creadas++;
        }

        $this->command->info("Jornadas de ejemplo creadas: $creadas");
        $this->command->warn('Son datos inventados. Para borrarlos: php artisan db:wipe --force y volver a sembrar.');
    }
}
