<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && ($usuario->esMaster() || $usuario->esAdministrador());
    }


    public function rules(): array
    {
        return [
            'nombre_usuario' => ['required', 'string', 'max:45', 'unique:usuarios,nombre_usuario'],
            'correo'         => ['required', 'email', 'max:120', 'unique:usuarios,correo'],
            'password'       => ['required', 'string', 'min:8', 'max:60'],
            'roles'          => ['required', 'array', 'min:1'],
            'roles.*'        => ['integer', 'exists:roles,id'],
            'hotel_id'       => ['nullable', 'integer', 'exists:hoteles,id'],
        ];
    }


    public function messages(): array
    {
        return [
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio',
            'nombre_usuario.unique'   => 'Ese nombre de usuario ya esta ocupado',
            'correo.required'         => 'El correo es obligatorio',
            'correo.email'            => 'El correo no tiene un formato valido',
            'correo.unique'           => 'Ese correo ya esta registrado',
            'password.required'       => 'La contrasena es obligatoria',
            'password.min'            => 'La contrasena debe tener al menos 8 caracteres',
            'roles.required'          => 'Debes elegir al menos un rol',
            'roles.min'               => 'Debes elegir al menos un rol',
            'roles.*.exists'          => 'Uno de los roles elegidos no existe',
        ];
    }


    protected  function prepareForValidation(): void
    {
        $this->merge([
            'nombre_usuario' => $this->nombreUsuario,
            'hotel_id'       => $this->hotelId,
        ]);
    }
}
