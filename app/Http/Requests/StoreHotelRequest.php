<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && ($usuario->esMaster() || $usuario->esAdministrador());
    }


    public function rules(): array
    {
        return [
            'nombre'            => ['required', 'string', 'max:120', 'unique:hoteles,nombre'],
            'direccion'         => ['nullable', 'string', 'max:150'],
            'telefono'          => ['nullable', 'string', 'max:45'],
            'contacto'          => ['nullable', 'string', 'max:120'],
            'hora_ronda_manana' => ['required', 'date_format:H:i'],
            'hora_ronda_tarde'  => ['required', 'date_format:H:i'],
        ];
    }


    public function messages(): array
    {
        return [
            'nombre.required'               => 'El nombre del hotel es obligatorio',
            'nombre.unique'                 => 'Ya existe un hotel con ese nombre',
            'hora_ronda_manana.required'    => 'Indica la hora de la ronda de la mañana',
            'hora_ronda_manana.date_format' => 'La hora de la mañana debe tener el formato HH:MM',
            'hora_ronda_tarde.required'     => 'Indica la hora de la ronda de la tarde',
            'hora_ronda_tarde.date_format'  => 'La hora de la tarde debe tener el formato HH:MM',
        ];
    }


    protected  function prepareForValidation(): void
    {
        $this->merge([
            'hora_ronda_manana' => $this->horaRondaManana,
            'hora_ronda_tarde'  => $this->horaRondaTarde,
        ]);
    }
}
