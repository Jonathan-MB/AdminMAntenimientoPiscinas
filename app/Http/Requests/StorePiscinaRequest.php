<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StorePiscinaRequest extends FormRequest
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
                Rule::unique('piscinas', 'nombre')->where('hotel_id', $hotelId),
            ],
            'orden'  => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }


    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la piscina es obligatorio',
            'nombre.unique'   => 'Ese hotel ya tiene una piscina con ese nombre',
            'orden.integer'   => 'El orden debe ser un número entero',
        ];
    }
}
