<?php

namespace Database\Seeders;

use App\Models\Cambio;
use App\Models\Dosis;
use App\Models\FotoTicket;
use App\Models\Hotel;
use App\Models\Jornada;
use App\Models\LecturaMetro;
use App\Models\Medicion;
use App\Models\MetroAgua;
use App\Models\MovimientoTicket;
use App\Models\Piscina;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Ronda;
use App\Models\RondaProgramada;
use App\Models\Tarea;
use App\Models\TareaRealizada;
use App\Models\Ticket;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    //  Una base con datos en todos lados, para probar las pantallas y sobre
    //  todo la impresion. Tres hoteles distintos entre si, varios dias de
    //  historial, correcciones, y tickets en los cuatro estados.
    //  NO se llama desde DatabaseSeeder: se corre a mano.
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('Este seeder no se corre en producción.');

            return;
        }

        $clave = env('PRUEBAS_PASSWORD', 'pruebas2026');

        $hoteles    = $this->hoteles();
        $personas   = $this->personas($hoteles, $clave);
        $productos  = Producto::orderBy('orden')->get();
        $tareas     = Tarea::where('activa', true)->orderBy('orden')->get();

        $jornadas = 0;

        foreach ($hoteles as $indice => $hotel) {
            $jornadas += $this->historial($hotel, $personas['colaboradores'], $productos, $tareas, $indice);
        }

        $this->tickets($hoteles, $personas);

        $this->command->info('Hoteles: ' . count($hoteles) . '. Jornadas: ' . $jornadas . '.');
        $this->command->info('Tickets: ' . Ticket::count() . ' (en los cuatro estados).');
        $this->command->warn('Contraseña de todas las cuentas de prueba: ' . $clave);
    }



    //  Tres hoteles con formas distintas: uno grande de dos rondas, uno de tres
    //  rondas y dos metros, y uno chico de una sola ronda.
    private function hoteles(): array
    {
        $definicion = [
            [
                'nombre'    => 'Aruba Hotel Enterprises N.V.',
                'direccion' => 'L.G. Smith Boulevard 82, Oranjestad',
                'telefono'  => '+297 583 4500',
                'contacto'  => 'Sra. Yolanda Croes',
                'piscinas'  => ['POOL VIP', 'POOL BAR', 'BIG POOL', 'SPA HOT', 'SPA COLD'],
                'rondas'    => [['Mañana', '06:00:00'], ['Tarde', '19:00:00']],
                'metros'    => ['Metro principal'],
            ],
            [
                'nombre'    => 'Palm Beach Resort & Spa',
                'direccion' => 'J.E. Irausquin Boulevard 230, Palm Beach',
                'telefono'  => '+297 586 1234',
                'contacto'  => 'Sr. Marcelo Tromp',
                'piscinas'  => ['Piscina principal', 'Piscina de niños', 'Jacuzzi'],
                'rondas'    => [['Mañana', '06:30:00'], ['Mediodía', '12:00:00'], ['Tarde', '18:30:00']],
                'metros'    => ['Metro norte', 'Metro sur'],
            ],
            [
                'nombre'    => 'Eagle Bay Suites',
                'direccion' => 'Sasakiweg 15, Eagle Beach',
                'telefono'  => '+297 587 9080',
                'contacto'  => 'Sra. Denise Wever',
                'piscinas'  => ['Pool lobby', 'Pool terraza'],
                'rondas'    => [['Mañana', '07:00:00']],
                'metros'    => ['Metro único'],
            ],
        ];

        $hoteles = [];

        foreach ($definicion as $datos) {
            //  updateOrCreate y no firstOrCreate: el hotel del HotelSeeder ya
            //  existe sin direccion ni telefono, y sin eso el membrete impreso
            //  sale a medias, que es justo lo que se quiere probar aqui.
            $hotel = Hotel::updateOrCreate(
                ['nombre' => $datos['nombre']],
                [
                    'direccion' => $datos['direccion'],
                    'telefono'  => $datos['telefono'],
                    'contacto'  => $datos['contacto'],
                ]
            );

            $orden = 1;

            foreach ($datos['piscinas'] as $nombre) {
                Piscina::firstOrCreate(
                    ['hotel_id' => $hotel->id, 'nombre' => $nombre],
                    ['orden' => $orden]
                );

                $orden++;
            }

            $orden = 1;

            foreach ($datos['rondas'] as $ronda) {
                RondaProgramada::firstOrCreate(
                    ['hotel_id' => $hotel->id, 'nombre' => $ronda[0]],
                    ['hora' => $ronda[1], 'orden' => $orden]
                );

                $orden++;
            }

            $orden = 1;

            foreach ($datos['metros'] as $nombre) {
                MetroAgua::firstOrCreate(
                    ['hotel_id' => $hotel->id, 'nombre' => $nombre],
                    ['orden' => $orden]
                );

                $orden++;
            }

            $hoteles[] = $hotel->fresh(['piscinas', 'rondasProgramadas', 'metrosAgua']);
        }

        return $hoteles;
    }



    //  Un usuario por rol, y uno de hotel por cada hotel
    private function personas(array $hoteles, string $clave): array
    {
        $colaboradores = [];

        foreach (['colab1', 'colab2', 'colab3'] as $nombre) {
            $colaboradores[] = $this->cuenta($nombre, Rol::COLABORADOR, $clave);
        }

        $porHotel = [];
        $nombres  = ['hotelaruba', 'hotelpalm', 'hoteleagle'];

        foreach ($hoteles as $i => $hotel) {
            $porHotel[] = $this->cuenta($nombres[$i], Rol::HOTEL, $clave, $hotel->id);
        }

        return [
            'admin'         => $this->cuenta('admin1', Rol::ADMINISTRADOR, $clave),
            'colaboradores' => $colaboradores,
            'hoteles'       => $porHotel,
            'jefe'          => $this->cuenta('jefe1', Rol::JEFE, $clave),
            'reparador'     => $this->cuenta('repa1', Rol::REPARACION, $clave),
        ];
    }



    private function cuenta(string $nombre, string $rolNombre, string $clave, ?int $hotelId = null): Usuario
    {
        $rol = Rol::where('nombre', $rolNombre)->firstOrFail();

        $usuario = Usuario::firstOrNew(['nombre_usuario' => $nombre]);
        $usuario->correo   = $nombre . '@aqualive.test';
        $usuario->password = $clave;
        $usuario->activo   = true;
        $usuario->hotel_id = $hotelId;
        $usuario->save();

        $usuario->roles()->sync([$rol->id]);

        return $usuario;
    }



    //  Dias de historial. El primer hotel lleva mas dias que los otros, para
    //  que el calendario y la paginacion tengan de donde.
    private function historial(Hotel $hotel, array $colaboradores, $productos, $tareas, int $indice): int
    {
        $dias = [22, 14, 9][$indice] ?? 10;

        $observaciones = [
            'Agua clara, sin novedad',
            'Se cepillaron los bordes del spa',
            'Nivel de agua bajo, se puso a llenar',
            'Filtro con presión alta, se hizo retrolavado',
            null,
        ];

        $materiales = [
            'Ácido muriático, tabletas de cloro',
            '2 galones de ácido, 1 caja de tabletas',
            'Cloro granulado y alguicida',
            null,
        ];

        $creadas = 0;

        for ($atras = 0; $atras < $dias; $atras++) {
            //  Se saltan algunos dias para que el calendario muestre huecos
            if (($atras + $indice) % 6 === 4) {
                continue;
            }

            $fecha = now()->subDays($atras)->toDateString();

            if (Jornada::where('hotel_id', $hotel->id)->whereDate('fecha', $fecha)->exists()) {
                continue;
            }

            //  Quien abre la jornada va rotando entre los colaboradores
            $principal = $colaboradores[($atras + $indice) % count($colaboradores)];

            $jornada = Jornada::create([
                'fecha'              => $fecha,
                'materiales_sacados' => $materiales[array_rand($materiales)],
                'hotel_id'           => $hotel->id,
                'usuario_id'         => $principal->id,
            ]);

            foreach ($hotel->metrosAgua as $i => $metro) {
                LecturaMetro::create([
                    'lectura'       => 145000 + $i * 8000 + $atras * 37.5,
                    'jornada_id'    => $jornada->id,
                    'metro_agua_id' => $metro->id,
                ]);
            }

            //  Un dia de cada tres lo reparten dos personas
            $reparten = $atras % 3 === 1;
            $segundo  = $colaboradores[($atras + $indice + 1) % count($colaboradores)];

            foreach ($hotel->rondasProgramadas as $programada) {
                $ronda = Ronda::create([
                    'hora'                => $programada->hora,
                    'observacion'         => $observaciones[array_rand($observaciones)],
                    'jornada_id'          => $jornada->id,
                    'ronda_programada_id' => $programada->id,
                ]);

                foreach ($hotel->piscinas as $posicion => $piscina) {
                    $autor = ($reparten && $posicion % 2 === 1) ? $segundo : $principal;

                    $medicion = new Medicion([
                        'cl_libre'        => round(mt_rand(80, 320) / 100, 2),
                        'cl_total'        => round(mt_rand(100, 360) / 100, 2),
                        'cl_combinado'    => round(mt_rand(0, 40) / 100, 2),
                        'ph'              => round(mt_rand(700, 800) / 100, 2),
                        'alcalinidad'     => mt_rand(70, 130),
                        'dureza_calcio'   => mt_rand(180, 400),
                        'acido_cianurico' => mt_rand(20, 60),
                        'retrolavado'     => mt_rand(0, 6) === 0,
                        'nivel_agua'      => ['normal', 'normal', 'normal', 'alto', 'bajo'][mt_rand(0, 4)],
                        'observacion'     => mt_rand(0, 4) === 0 ? 'Se limpió el skimmer' : null,
                        'ronda_id'        => $ronda->id,
                        'piscina_id'      => $piscina->id,
                    ]);

                    $medicion->usuario_id = $autor->id;
                    $medicion->save();

                    foreach ($productos->random(mt_rand(0, 2)) as $producto) {
                        Dosis::create([
                            'cantidad'    => round(mt_rand(25, 300) / 100, 2),
                            'medicion_id' => $medicion->id,
                            'producto_id' => $producto->id,
                        ]);
                    }
                }
            }

            //  El listado de trabajo: la mayoria marcadas, algunas no
            foreach ($tareas as $posicion => $tarea) {
                if ($posicion % 7 === 6) {
                    continue;
                }

                TareaRealizada::create([
                    'hecha'      => $posicion % 5 !== 3,
                    'marcada_en' => $posicion % 5 !== 3 ? now()->subDays($atras) : null,
                    'jornada_id' => $jornada->id,
                    'tarea_id'   => $tarea->id,
                ]);
            }

            //  Una correccion cada cuatro dias, para ver la fila amarilla
            if ($atras % 4 === 2) {
                $piscina = $hotel->piscinas->first();
                $ronda   = $hotel->rondasProgramadas->first();

                Cambio::create([
                    'donde'          => $piscina->nombre . ' · ' . $ronda->nombre,
                    'campo'          => 'Cloro libre',
                    'valor_anterior' => '1.20',
                    'valor_nuevo'    => '2.40',
                    'jornada_id'     => $jornada->id,
                    'usuario_id'     => $principal->id,
                ]);
            }

            $creadas++;
        }

        return $creadas;
    }



    //  Tickets en los cuatro estados, repartidos entre los hoteles, con su
    //  historial de movimientos y alguna foto.
    private function tickets(array $hoteles, array $personas): void
    {
        $reparador = $personas['reparador'];
        $jefe      = $personas['jefe'];

        $definicion = [
            ['Bomba del spa hace ruido',        'Se escucha un golpeteo al arrancar.',           Ticket::POR_HACER,    0, 2],
            ['Luz de la piscina fundida',       'La luz del fondo no enciende desde el lunes.',  Ticket::POR_HACER,    1, 0],
            ['Escalera suelta en el jacuzzi',   'Se mueve al pisarla, riesgo para el huésped.',  Ticket::POR_HACER,    2, 1],
            ['Fuga en la ducha exterior',       'Gotea constante, se cambió el empaque.',        Ticket::POR_FACTURAR, 0, 0],
            ['Filtro de arena con fisura',      'Se reemplazó el filtro completo.',              Ticket::POR_FACTURAR, 1, 2],
            ['Motor de la bomba principal',     'Se rebobinó el motor y quedó funcionando.',     Ticket::POR_COBRAR,   0, 1],
            ['Reja del cuarto de máquinas',     'Se soldó la reja y se pintó.',                  Ticket::POR_COBRAR,   2, 0],
            ['Cambio de tablero eléctrico',     'Tablero nuevo, instalado y probado.',           Ticket::COBRADO,      0, 0],
            ['Bomba dosificadora sin cebar',    'Se limpió y se cebó, quedó operando.',          Ticket::COBRADO,      1, 1],
        ];

        $orden = Ticket::estadosAbiertos();
        $orden[] = Ticket::COBRADO;

        foreach ($definicion as $i => $datos) {
            list($titulo, $observacion, $estadoFinal, $hotelIndice, $conFotos) = $datos;

            $hotel = $hoteles[$hotelIndice];

            if (Ticket::where('titulo', $titulo)->exists()) {
                continue;
            }

            $ticket = Ticket::create([
                'titulo'      => $titulo,
                'observacion' => $observacion,
                'estado'      => $estadoFinal,
                'hotel_id'    => $hotel->id,
                'usuario_id'  => $reparador->id,
            ]);

            $ticket->created_at = now()->subDays(12 - $i);
            $ticket->save();

            //  El historial: la creacion y cada paso hasta el estado final
            $anterior = null;
            $cuando   = now()->subDays(12 - $i);

            foreach ($orden as $estado) {
                MovimientoTicket::create([
                    'estado_anterior' => $anterior,
                    'estado_nuevo'    => $estado,
                    'ticket_id'       => $ticket->id,
                    'usuario_id'      => $estado === Ticket::COBRADO ? $jefe->id : $reparador->id,
                    'created_at'      => $cuando,
                ]);

                if ($estado === $estadoFinal) {
                    break;
                }

                $anterior = $estado;
                $cuando   = $cuando->copy()->addDays(2);
            }

            for ($f = 0; $f < $conFotos; $f++) {
                $this->foto($ticket, $reparador);
            }
        }
    }



    //  Una foto de mentira, pero un PNG de verdad: si no, la ruta que las
    //  sirve devuelve 404 y no se puede probar la galeria.
    private function foto(Ticket $ticket, Usuario $autor): void
    {
        $nombre = Str::uuid() . '.png';
        $ruta   = 'tickets/' . $ticket->id . '/' . $nombre;

        Storage::disk('local')->put($ruta, $this->png(mt_rand(60, 200), mt_rand(120, 220), mt_rand(180, 250)));

        FotoTicket::create([
            'ruta'            => $ruta,
            'nombre_original' => 'foto-' . $ticket->id . '-' . mt_rand(100, 999) . '.png',
            'ticket_id'       => $ticket->id,
            'usuario_id'      => $autor->id,
        ]);
    }



    //  PNG de un solo color, escrito a mano: este servidor no tiene GD
    private function png(int $r, int $g, int $b, int $ancho = 480, int $alto = 320): string
    {
        $crudo = '';

        for ($y = 0; $y < $alto; $y++) {
            $crudo .= chr(0) . str_repeat(chr($r) . chr($g) . chr($b), $ancho);
        }

        $trozo = function (string $tipo, string $cuerpo) {
            return pack('N', strlen($cuerpo)) . $tipo . $cuerpo . pack('N', crc32($tipo . $cuerpo));
        };

        return "\x89PNG\r\n\x1a\n"
            . $trozo('IHDR', pack('NNCCCCC', $ancho, $alto, 8, 2, 0, 0, 0))
            . $trozo('IDAT', gzcompress($crudo, 9))
            . $trozo('IEND', '');
    }
}
