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
            'nombre'    => ['required', 'string', 'max:120', 'unique:hoteles,nombre'],
            'direccion' => ['nullable', 'string', 'max:150'],
            'telefono'  => ['nullable', 'string', 'max:45'],
            'contacto'  => ['nullable', 'string', 'max:120'],
        ];
    }


    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del hotel es obligatorio',
            'nombre.unique'   => 'Ya existe un hotel con ese nombre',
        ];
    }
}
