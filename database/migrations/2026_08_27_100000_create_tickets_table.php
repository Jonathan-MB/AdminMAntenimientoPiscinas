<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Un ticket de reparacion. El estado avanza por hacer, por facturar,
        //  por cobrar y cobrado; al llegar a cobrado sale de la lista y pasa
        //  al historial.
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('titulo',120);
            $table->text('observacion')->nullable();
            $table->string('estado',20)->default('por_hacer');

            $table->foreignId('hotel_id')->constrained('hoteles')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();

            $table->timestamps();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
