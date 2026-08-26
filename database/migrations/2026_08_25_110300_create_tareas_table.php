<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  El listado de trabajo diario es uno solo para toda la operacion
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->string('bloque',45);
            $table->time('hora');
            $table->string('descripcion',255);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activa')->default(true);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
