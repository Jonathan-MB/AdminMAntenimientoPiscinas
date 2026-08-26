<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas_realizadas', function (Blueprint $table) {
            $table->id();
            $table->boolean('hecha')->default(false);
            $table->timestamp('marcada_en')->nullable();

            $table->foreignId('jornada_id')->constrained('jornadas')->restrictOnDelete();
            $table->foreignId('tarea_id')->constrained('tareas')->restrictOnDelete();

            $table->timestamps();

            $table->unique(['jornada_id', 'tarea_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas_realizadas');
    }
};
