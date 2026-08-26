<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  La ronda que efectivamente se hizo ese dia, apuntando a la que estaba programada
        Schema::create('rondas', function (Blueprint $table) {
            $table->id();
            $table->time('hora');
            $table->text('observacion')->nullable();

            $table->foreignId('jornada_id')->constrained('jornadas')->restrictOnDelete();
            $table->foreignId('ronda_programada_id')->constrained('rondas_programadas')->restrictOnDelete();

            $table->timestamps();

            $table->unique(['jornada_id', 'ronda_programada_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rondas');
    }
};
