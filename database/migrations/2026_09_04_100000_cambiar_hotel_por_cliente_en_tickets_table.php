<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  El cliente de una reparacion puede ser ajeno a los hoteles que usan
        //  el sistema (una residencia, un restaurante), asi que deja de ser
        //  una llave foranea y pasa a ser texto libre.
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('cliente',150)->nullable()->after('estado');
            $table->string('direccion',200)->nullable()->after('cliente');
        });

        //  Los tickets que ya existen se quedan con el nombre y la direccion
        //  del hotel que tenian: sin esto se perderia de quien era cada uno.
        DB::table('tickets')
            ->join('hoteles', 'hoteles.id', '=', 'tickets.hotel_id')
            ->update([
                'tickets.cliente'   => DB::raw('hoteles.nombre'),
                'tickets.direccion' => DB::raw('hoteles.direccion'),
            ]);

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropColumn('hotel_id');
        });

        //  Obligatoria solo despues de rellenarla, o la copia de arriba no
        //  tendria donde caer.
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('cliente',150)->nullable(false)->change();
        });
    }


    public function down(): void
    {
        //  Vuelve la columna, pero el vinculo con cada hotel no se puede
        //  rehacer: el cliente era texto y podia no ser ninguno de ellos.
        //  Por eso entra nullable y no como estaba.
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable()->after('estado')
                  ->constrained('hoteles')->restrictOnDelete();

            $table->dropColumn(['cliente', 'direccion']);
        });
    }
};
