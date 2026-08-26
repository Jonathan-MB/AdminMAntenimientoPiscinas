<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        //  Los químicos del formato de pruebas, con la unidad tal como está impresa.
        //  "back Wash" no está aquí: es una acción, va como booleano en mediciones.
        $productos = [
            ['nombre' => 'Ácido muriático',      'unidad' => 'gallon'],
            ['nombre' => 'Alguicida',            'unidad' => 'und'],
            ['nombre' => 'Super blue',           'unidad' => 'und'],
            ['nombre' => 'Cloro granulado',      'unidad' => '1.5 lb / cup'],
            ['nombre' => 'Tricloro',             'unidad' => 'cup'],
            ['nombre' => 'Tabletas 3"',          'unidad' => 'und'],
            ['nombre' => 'Bicarbonato de sodio', 'unidad' => 'pack'],
            ['nombre' => 'Ácido cianúrico',      'unidad' => 'lb'],
            ['nombre' => 'Balance fosfato',      'unidad' => 'und'],
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
