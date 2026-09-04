<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Que decia la observacion de un ticket antes de cada edicion, y quien
        //  la cambio. La observacion se va corrigiendo mientras avanza la
        //  reparacion, y sin esto lo anterior se perderia sin dejar rastro.
        Schema::create('ediciones_observacion_ticket', function (Blueprint $table) {
            $table->id();
            $table->text('texto_anterior')->nullable();
            $table->text('texto_nuevo')->nullable();

            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();

            $table->timestamps();

            $table->index('ticket_id');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('ediciones_observacion_ticket');
    }
};
