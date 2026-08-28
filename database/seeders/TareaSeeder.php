<?php

namespace Database\Seeders;

use App\Models\Tarea;
use Illuminate\Database\Seeder;

class TareaSeeder extends Seeder
{
    public function run(): void
    {
        //  Listado de trabajo diario, transcrito del formato en papel
        $bloques = [
            ['bloque' => '4:00 AM', 'hora' => '04:00:00', 'tareas' => [
                'Verificar limpieza de filtros y organizar máquinas para hacer limpieza de piscinas',
                'Verificación del nivel de agua',
                'Si hace falta agua, poner a llenar la piscina',
                'Limpieza de piscinas',
            ]],

            ['bloque' => '6:00 AM', 'hora' => '06:00:00', 'tareas' => [
                'Realizar las pruebas químicas al agua de la piscina',
                'Llenar los formatos según los resultados',
                'Tomar también la secuencia del lleno de agua de la piscina',
                'Suministrar los químicos necesarios según resultado de pruebas químicas',
            ]],

            ['bloque' => '6:30 AM', 'hora' => '06:30:00', 'tareas' => [
                'Limpieza de bordes, skimmer y cerámica',
                'Cepillada de los lugares que sea necesario',
            ]],

            ['bloque' => '7:00 AM', 'hora' => '07:00:00', 'tareas' => [
                'Limpieza de los filtros después de hacer limpieza a las piscinas',
                'Limpieza de la arena acumulada en el canal',
                'Limpieza del cuarto de máquina',
                'Limpieza de las canastas de las bombas',
                'Retirar hojas, vasos y basura de la superficie de la piscina',
            ]],

            ['bloque' => '8:00 AM - Antes de salir', 'hora' => '08:00:00', 'tareas' => [
                'Verificar que los niveles de agua de la piscina estén bien',
                'Verificar que las bombas quedan funcionando',
                'Verificar que las luces estén apagadas',
                'Verificar que en las piscinas todo esté limpio',
                'Entregar formato de pruebas químicas',
            ]],

            ['bloque' => '7:00 PM', 'hora' => '19:00:00', 'tareas' => [
                'Realizar las pruebas químicas al agua de la piscina',
                'Llenar los formatos según los resultados',
                'Tomar también la secuencia del lleno de agua de la piscina',
                'Suministrar los químicos necesarios según resultado de pruebas químicas',
            ]],
        ];

        $orden = 1;

        foreach ($bloques as $bloque) {
            foreach ($bloque['tareas'] as $descripcion) {
                Tarea::firstOrCreate(
                    ['descripcion' => $descripcion, 'bloque' => $bloque['bloque']],
                    ['hora' => $bloque['hora'], 'orden' => $orden]
                );

                $orden++;
            }
        }
    }
}
