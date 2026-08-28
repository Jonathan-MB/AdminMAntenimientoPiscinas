<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Que se cambio de una jornada despues de haberla guardado.
        //  Solo se anota cuando el campo YA tenia valor: llenar un campo vacio
        //  por primera vez no es una correccion.
        Schema::create('cambios', function (Blueprint $table) {
            $table->id();
            $table->string('donde',150);
            $table->string('campo',60);
            $table->string('valor_anterior',100)->nullable();
            $table->string('valor_nuevo',100)->nullable();

            $table->foreignId('jornada_id')->constrained('jornadas')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();

            $table->timestamps();

            $table->index('jornada_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cambios');
    }
};
