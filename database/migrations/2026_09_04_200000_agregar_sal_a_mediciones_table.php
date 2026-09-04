<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  La sal se mide como las demas lecturas, en ppm, y ronda los miles:
        //  por eso el mismo tamaño que la alcalinidad y no el del pH.
        Schema::table('mediciones', function (Blueprint $table) {
            $table->decimal('sal', 7, 2)->nullable()->after('acido_cianurico');
        });

        //  Y ademas se echa, asi que va tambien como producto: una cosa es con
        //  cuanta empieza la piscina y otra cuanta se le agrega.
        //
        //  Solo si el catalogo ya existe. En una base nueva las migraciones
        //  corren antes que los seeders, y meterla aqui con la tabla vacia le
        //  daria el orden 1: la sal saldria de primera en la pantalla, delante
        //  del acido muriatico. En ese caso la crea ProductoSeeder, en su sitio.
        $hayCatalogo = DB::table('productos')->exists();
        $yaEsta      = DB::table('productos')->where('nombre', 'Sal')->exists();

        if ($hayCatalogo && ! $yaEsta) {
            DB::table('productos')->insert([
                'nombre'     => 'Sal',
                'unidad'     => 'kilos',
                'orden'      => DB::table('productos')->max('orden') + 1,
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }


    public function down(): void
    {
        Schema::table('mediciones', function (Blueprint $table) {
            $table->dropColumn('sal');
        });

        //  Solo si nadie la uso: borrarla con dosis colgando se llevaria por
        //  delante lo que ya se registro.
        $sal = DB::table('productos')->where('nombre', 'Sal')->first();

        if ($sal && ! DB::table('dosis')->where('producto_id', $sal->id)->exists()) {
            DB::table('productos')->where('id', $sal->id)->delete();
        }
    }
};
