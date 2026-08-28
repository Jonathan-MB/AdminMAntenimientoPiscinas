<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Una jornada es la hoja de papel: un dia, un hotel.
        //  Las lecturas de metro van aparte: cada hotel tiene los suyos.
        Schema::create('jornadas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->text('materiales_sacados')->nullable();
            $table->boolean('entregada')->default(false);

            $table->foreignId('hotel_id')->constrained('hoteles')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();

            $table->timestamps();

            $table->unique(['hotel_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jornadas');
    }
};
