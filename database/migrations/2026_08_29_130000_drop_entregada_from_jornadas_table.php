<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Venia del formato en papel, que se "entrega" al final del turno.
        //  Nunca se escribio ni se leyo desde ninguna pantalla, asi que sale.
        //  Si algun dia el turno se cierra de verdad, se vuelve a agregar
        //  junto con lo que signifique cerrarlo.
        Schema::table('jornadas', function (Blueprint $table) {
            $table->dropColumn('entregada');
        });
    }

    public function down(): void
    {
        Schema::table('jornadas', function (Blueprint $table) {
            $table->boolean('entregada')->default(false)->after('materiales_sacados');
        });
    }
};
