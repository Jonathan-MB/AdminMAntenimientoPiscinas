<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Un usuario puede tener varios roles. El rol master es la excepcion:
        //  quien lo tiene no lleva ningun otro, y eso se cuida en el controlador.
        Schema::create('rol_usuario', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['rol_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_usuario');
    }
};
