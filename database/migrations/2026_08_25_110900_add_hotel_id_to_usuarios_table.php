<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Solo lo usan los usuarios con rol hotel: es el hotel que pueden consultar
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable()->after('activo')->constrained('hoteles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hotel_id');
        });
    }
};
