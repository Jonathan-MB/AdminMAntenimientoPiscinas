<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Piscina;
use App\Models\RondaProgramada;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        //  Hotel, piscinas y rondas transcritos del formato en papel.
        //  Si el hotel se administra desde la pantalla, este seeder se puede quitar.
        $hotel = Hotel::firstOrCreate(['nombre' => 'Aruba Hotel Enterprises N.V.']);

        $piscinas = [
            'POOL VIP',
            'POOL BAR',
            'BIG POOL',
            'SPA HOT',
            'SPA COLD',
        ];

        $orden = 1;

        foreach ($piscinas as $nombre) {
            Piscina::firstOrCreate(
                ['hotel_id' => $hotel->id, 'nombre' => $nombre],
                ['orden' => $orden]
            );

            $orden++;
        }

        //  Las dos rondas del formato. Se pueden agregar mas desde la pantalla.
        $rondas = [
            ['nombre' => 'Mañana', 'hora' => '06:00:00', 'orden' => 1],
            ['nombre' => 'Tarde',  'hora' => '19:00:00', 'orden' => 2],
        ];

        foreach ($rondas as $ronda) {
            RondaProgramada::firstOrCreate(
                ['hotel_id' => $hotel->id, 'nombre' => $ronda['nombre']],
                ['hora' => $ronda['hora'], 'orden' => $ronda['orden']]
            );
        }
    }
}
