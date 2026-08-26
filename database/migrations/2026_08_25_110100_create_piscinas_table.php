<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piscinas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',45);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activa')->default(true);

            $table->foreignId('hotel_id')->constrained('hoteles')->restrictOnDelete();

            $table->timestamps();

            $table->unique(['hotel_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piscinas');
    }
};
