<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Una lectura por metro y por jornada
        Schema::create('lecturas_metro', function (Blueprint $table) {
            $table->id();
            $table->decimal('lectura', 12, 2)->nullable();

            $table->foreignId('jornada_id')->constrained('jornadas')->restrictOnDelete();
            $table->foreignId('metro_agua_id')->constrained('metros_agua')->restrictOnDelete();

            $table->timestamps();

            $table->unique(['jornada_id', 'metro_agua_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturas_metro');
    }
};
