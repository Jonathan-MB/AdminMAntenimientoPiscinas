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
            'cliente'     => ['required', 'string', 'max:150'],
            'direccion'   => ['nullable', 'string', 'max:200'],
            'titulo'      => ['required', 'string', 'max:120'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ];
    }


    public function messages(): array
    {
        return [
            'cliente.required' => 'Escribe el nombre del cliente',
            'cliente.max'      => 'El nombre del cliente no puede pasar de 150 caracteres',
            'titulo.required'  => 'Escribe de qué se trata la reparación',
            'titulo.max'       => 'El título no puede pasar de 120 caracteres',
        ];
    }
}
