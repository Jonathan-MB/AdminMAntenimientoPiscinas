<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRondaProgramadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && ($usuario->esMaster() || $usuario->esAdministrador());
    }


    public function rules(): array
    {
        $ronda = $this->route('rondaProgramada');

        $unico = Rule::unique('rondas_programadas', 'nombre')
            ->where('hotel_id', $ronda->hotel_id)
            ->ignore($ronda->id);

        if ($this->isMethod('put')) {
            return [
                'nombre' => ['required', 'string', 'max:45', $unico],
                'hora'   => ['required', 'date_format:H:i'],
                'orden'  => ['required', 'integer', 'min:0', 'max:999'],
                'activa' => ['required', 'boolean'],
            ];
        }

        //PATCH
        return [
            'nombre' => ['sometimes', 'string', 'max:45', $unico],
            'hora'   => ['sometimes', 'date_format:H:i'],
            'orden'  => ['sometimes', 'integer', 'min:0', 'max:999'],
            'activa' => ['sometimes', 'boolean'],
        ];
    }


    public function messages(): array
    {
        return [
            'nombre.unique'    => 'Ese hotel ya tiene una ronda con ese nombre',
            'hora.date_format' => 'La hora debe tener el formato HH:MM',
            'orden.integer'    => 'El orden debe ser un número entero',
        ];
    }
}
