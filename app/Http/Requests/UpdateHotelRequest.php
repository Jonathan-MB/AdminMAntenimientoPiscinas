<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && ($usuario->esMaster() || $usuario->esAdministrador());
    }


    public function rules(): array
    {
        $id = $this->route('hotel')->id;

        if ($this->isMethod('put')) {
            return [
                'nombre'    => ['required', 'string', 'max:120', Rule::unique('hoteles', 'nombre')->ignore($id)],
                'direccion' => ['nullable', 'string', 'max:150'],
                'telefono'  => ['nullable', 'string', 'max:45'],
                'contacto'  => ['nullable', 'string', 'max:120'],
                'activo'    => ['required', 'boolean'],
            ];
        }

        //PATCH
        return [
            'nombre'    => ['sometimes', 'string', 'max:120', Rule::unique('hoteles', 'nombre')->ignore($id)],
            'direccion' => ['sometimes', 'nullable', 'string', 'max:150'],
            'telefono'  => ['sometimes', 'nullable', 'string', 'max:45'],
            'contacto'  => ['sometimes', 'nullable', 'string', 'max:120'],
            'activo'    => ['sometimes', 'boolean'],
        ];
    }


    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un hotel con ese nombre',
        ];
    }
}
