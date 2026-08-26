<?php

namespace App\Http\Requests;

use App\Models\Rol;
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
        $this->merge([
            'hotel_id' => $this->hotelId,
        ]);
    }
}
