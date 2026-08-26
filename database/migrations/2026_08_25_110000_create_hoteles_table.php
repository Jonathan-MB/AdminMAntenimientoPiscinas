<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoteles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',120)->unique();
            $table->string('direccion',150)->nullable();
            $table->string('telefono',45)->nullable();
            $table->string('contacto',120)->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoteles');
    }
};
