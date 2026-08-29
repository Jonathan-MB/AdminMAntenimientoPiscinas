<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Quien registro esta medicion. Hasta ahora la jornada guardaba un solo
        //  usuario, el que la abrio, asi que si dos colaboradores se repartian el
        //  dia todo quedaba a nombre de uno.
        Schema::table('mediciones', function (Blueprint $table) {
            $table->foreignId('usuario_id')->nullable()->after('piscina_id')
                ->constrained('usuarios')->restrictOnDelete();
        });

        //  Lo que ya estaba se atribuye a quien abrio la jornada, que es lo unico
        //  que se sabe de esas filas
        DB::statement('
            UPDATE mediciones m
            JOIN rondas r ON r.id = m.ronda_id
            JOIN jornadas j ON j.id = r.jornada_id
            SET m.usuario_id = j.usuario_id
            WHERE m.usuario_id IS NULL
        ');

        //  Desde aqui siempre viene puesto
        Schema::table('mediciones', function (Blueprint $table) {
            $table->foreignId('usuario_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('mediciones', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropColumn('usuario_id');
        });
    }
};
