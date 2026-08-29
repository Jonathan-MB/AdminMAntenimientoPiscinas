<?php

namespace App\Http\Requests;

use App\Models\Rol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && $usuario->tieneRol(Rol::MASTER, Rol::JEFE, Rol::REPARACION);
    }


    public function rules(): array
    {
        return [
            'hotel_id'    => ['required', 'integer', 'exists:hoteles,id'],
            'titulo'      => ['required', 'string', 'max:120'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ];
    }


    public function messages(): array
    {
        return [
            'hotel_id.required' => 'Elige el hotel',
            'hotel_id.exists'   => 'Ese hotel no existe',
            'titulo.required'   => 'Escribe de qué se trata la reparación',
            'titulo.max'        => 'El título no puede pasar de 120 caracteres',
        ];
    }


    protected  function prepareForValidation(): void
    {
        $this->merge([
            'hotel_id' => $this->hotelId,
        ]);
    }
}
