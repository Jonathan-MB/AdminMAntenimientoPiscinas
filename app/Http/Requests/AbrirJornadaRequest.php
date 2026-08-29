<?php

namespace App\Http\Requests;

use App\Models\Rol;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AbrirJornadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && $usuario->tieneRol(Rol::MASTER, Rol::ADMINISTRADOR, Rol::COLABORADOR);
    }


    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'integer', 'exists:hoteles,id'],
            'fecha'    => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }


    public function messages(): array
    {
        return [
            'hotel_id.required'      => 'Elige el hotel',
            'hotel_id.exists'        => 'Ese hotel no existe',
            'fecha.required'         => 'Elige la fecha',
            'fecha.date_format'      => 'La fecha no tiene un formato válido',
            'fecha.before_or_equal'  => 'No se puede abrir una jornada de un día futuro',
        ];
    }


    protected  function prepareForValidation(): void
    {
        $datos = ['hotel_id' => $this->hotelId];

        //  El colaborador solo registra el dia de hoy. No es solo que la
        //  pantalla no le muestre el campo: aunque lo mande a mano, se ignora.
        //  Abrir una jornada de otro dia le crearia una que despues no puede
        //  editar, porque la ventana de correccion es del mismo dia.
        $usuario = Auth::user();

        if ($usuario && ! $usuario->esMaster() && ! $usuario->esAdministrador()) {
            $datos['fecha'] = Carbon::today()->toDateString();
        }

        $this->merge($datos);
    }
}
