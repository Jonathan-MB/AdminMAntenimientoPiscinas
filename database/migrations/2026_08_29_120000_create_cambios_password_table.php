<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Quien le cambio la contraseña a quien y cuando. Sin la contraseña,
        //  claro: aqui solo se guarda el hecho de que cambio.
        Schema::create('cambios_password', function (Blueprint $table) {
            $table->id();

            //  Si se borra el usuario, su historial se va con el: sin cuenta no
            //  significa nada. Si se borra quien lo hizo, la linea se queda con
            //  el autor en nulo, porque el hecho si importa.
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('autor_id')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->timestamp('created_at')->nullable();

            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cambios_password');
    }
};
