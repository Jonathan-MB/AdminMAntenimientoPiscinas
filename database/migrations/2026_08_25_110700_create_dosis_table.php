<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Cuanto se aplico de cada quimico en esa medicion
        Schema::create('dosis', function (Blueprint $table) {
            $table->id();
            $table->decimal('cantidad', 8, 2);

            $table->foreignId('medicion_id')->constrained('mediciones')->restrictOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();

            $table->timestamps();

            $table->unique(['medicion_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosis');
    }
};
