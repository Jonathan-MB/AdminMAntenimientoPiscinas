<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Quien movio el ticket, a que estado y cuando. El primer movimiento
        //  es la creacion, con estado anterior nulo.
        Schema::create('movimientos_ticket', function (Blueprint $table) {
            $table->id();
            $table->string('estado_anterior',20)->nullable();
            $table->string('estado_nuevo',20);

            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();

            $table->timestamps();

            $table->index('ticket_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_ticket');
    }
};
