<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreRondaProgramadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && ($usuario->esMaster() || $usuario->esAdministrador());
    }


    public function rules(): array
    {
        $hotelId = $this->route('hotel')->id;

        return [
            'nombre' => [
                'required',
                'string',
                'max:45',
                Rule::unique('rondas_programadas', 'nombre')->where('hotel_id', $hotelId),
            ],
            'hora'   => ['required', 'date_format:H:i'],
            'orden'  => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }


    public function messages(): array
    {
        return [
            'nombre.required'   => 'Ponle un nombre a la ronda',
            'nombre.unique'     => 'Ese hotel ya tiene una ronda con ese nombre',
            'hora.required'     => 'Indica la hora de la ronda',
            'hora.date_format'  => 'La hora debe tener el formato HH:MM',
            'orden.integer'     => 'El orden debe ser un número entero',
        ];
    }
}
