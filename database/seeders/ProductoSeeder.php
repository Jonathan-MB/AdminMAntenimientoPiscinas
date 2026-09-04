<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        //  Los químicos del formato de pruebas. La unidad es la que se usa al
        //  dosificar en piscina: líquidos en galones u onzas según el tamaño de
        //  la dosis, sólidos en libras o kilos. Las tabletas se cuentan, no se
        //  pesan. La sal va aquí porque se echa, y además se mide: el nivel con
        //  el que empieza la piscina es una lectura, en mediciones.
        //  "back Wash" no está aquí: es una acción, va como booleano en mediciones.
        $productos = [
            ['nombre' => 'Ácido muriático',      'unidad' => 'galones'],
            ['nombre' => 'Alguicida',            'unidad' => 'onzas'],
            ['nombre' => 'Super blue',           'unidad' => 'onzas'],
            ['nombre' => 'Cloro granulado',      'unidad' => 'libras'],
            ['nombre' => 'Tricloro',             'unidad' => 'libras'],
            ['nombre' => 'Tabletas 3"',          'unidad' => 'tabletas'],
            ['nombre' => 'Bicarbonato de sodio', 'unidad' => 'libras'],
            ['nombre' => 'Ácido cianúrico',      'unidad' => 'kilos'],
            ['nombre' => 'Balance fosfato',      'unidad' => 'onzas'],
            ['nombre' => 'Sal',                  'unidad' => 'kilos'],
        ];

        $orden = 1;

        foreach ($productos as $producto) {
            Producto::firstOrCreate(
                ['nombre' => $producto['nombre']],
                ['unidad' => $producto['unidad'], 'orden' => $orden]
            );

            $orden++;
        }
    }
}
