<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Piscina;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        //  Hotel y piscinas transcritos del formato en papel.
        //  Si el hotel se administra desde la pantalla, este seeder se puede quitar.
        $hotel = Hotel::firstOrCreate(
            ['nombre' => 'Aruba Hotel Enterprises N.V.'],
            [
                'hora_ronda_manana' => '06:00:00',
                'hora_ronda_tarde'  => '19:00:00',
            ]
        );

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
    }
}
