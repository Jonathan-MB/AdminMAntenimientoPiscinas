<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Las siete lecturas del formato, una fila por piscina y ronda
        Schema::create('mediciones', function (Blueprint $table) {
            $table->id();
            $table->decimal('cl_libre', 6, 2)->nullable();
            $table->decimal('cl_total', 6, 2)->nullable();
            $table->decimal('cl_combinado', 6, 2)->nullable();
            $table->decimal('ph', 4, 2)->nullable();
            $table->decimal('alcalinidad', 7, 2)->nullable();
            $table->decimal('dureza_calcio', 7, 2)->nullable();
            $table->decimal('acido_cianurico', 7, 2)->nullable();

            //  back Wash del formato: es una accion, no una cantidad
            $table->boolean('retrolavado')->default(false);

            //  Nivel del agua de la piscina: alto, normal o bajo
            $table->string('nivel_agua',10)->default('normal');

            $table->string('observacion',255)->nullable();

            $table->foreignId('ronda_id')->constrained('rondas')->restrictOnDelete();
            $table->foreignId('piscina_id')->constrained('piscinas')->restrictOnDelete();

            $table->timestamps();

            $table->unique(['ronda_id', 'piscina_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediciones');
    }
};
