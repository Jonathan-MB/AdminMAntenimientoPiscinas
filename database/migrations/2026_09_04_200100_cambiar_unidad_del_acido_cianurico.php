<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        //  El acido cianurico se dosifica en kilos, no en libras. Solo cambia
        //  la etiqueta: las cantidades ya registradas se quedan como estan,
        //  porque nadie puede saber hoy si se anotaron pensando en una unidad
        //  o en la otra. Si hiciera falta convertirlas, es una decision de
        //  quien lo aplico, no de una migracion.
        DB::table('productos')
            ->where('nombre', 'Ácido cianúrico')
            ->update(['unidad' => 'kilos', 'updated_at' => now()]);
    }


    public function down(): void
    {
        DB::table('productos')
            ->where('nombre', 'Ácido cianúrico')
            ->update(['unidad' => 'libras', 'updated_at' => now()]);
    }
};
