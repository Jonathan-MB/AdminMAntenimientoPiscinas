<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rondas', function (Blueprint $table) {
            $table->id();
            $table->string('turno',10);
            $table->time('hora');
            $table->text('observacion')->nullable();

            $table->foreignId('jornada_id')->constrained('jornadas')->restrictOnDelete();

            $table->timestamps();

            $table->unique(['jornada_id', 'turno']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rondas');
    }
};
