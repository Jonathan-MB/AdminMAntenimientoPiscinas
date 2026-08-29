<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Las fotos no viven en public: la ruta guarda el archivo dentro de
        //  storage y se sirve por una ruta con permiso, como el resto del modulo.
        Schema::create('fotos_ticket', function (Blueprint $table) {
            $table->id();
            $table->string('ruta',180);
            $table->string('nombre_original',120);

            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();

            $table->timestamps();

            $table->index('ticket_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fotos_ticket');
    }
};
