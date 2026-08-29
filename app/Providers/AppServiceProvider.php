<?php

namespace App\Providers;

use App\Models\Rol;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
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

        //  La barra lleva cuantas reparaciones estan abiertas. Se calcula aqui y
        //  no dentro del blade para que la vista no consulte la base.
        View::composer('partials.header', function ($vista) {
            $usuario  = Auth::user();
            $abiertos = null;

            if ($usuario && $usuario->tieneRol(Rol::MASTER, Rol::JEFE, Rol::REPARACION)) {
                $abiertos = Ticket::whereIn('estado', Ticket::estadosAbiertos())->count();
            }

            $vista->with('reparacionesAbiertas', $abiertos);
        });
    }
}
