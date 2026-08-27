<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }


    public function boot(): void
    {
        //  @recurso('css/panel.css') devuelve la ruta con la fecha del archivo detras.
        //  Sin esto el navegador sigue sirviendo la hoja vieja despues de cada cambio.
        Blade::directive('recurso', function ($ruta) {
            return "<?php echo asset($ruta) . '?v=' . (file_exists(public_path($ruta)) ? filemtime(public_path($ruta)) : '1'); ?>";
        });
    }
}
