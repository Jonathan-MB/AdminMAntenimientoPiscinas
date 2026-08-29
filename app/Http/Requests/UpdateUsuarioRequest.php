<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && ($usuario->esMaster() || $usuario->esAdministrador());
    }


    public function rules(): array
    {
        $id = $this->route('usuario')->id;

        if ($this->isMethod('put')) {
            return [
                'nombre_usuario' => ['required', 'string', 'max:45', Rule::unique('usuarios', 'nombre_usuario')->ignore($id)],
                'correo'         => ['required', 'email', 'max:120', Rule::unique('usuarios', 'correo')->ignore($id)],
                'password'       => ['nullable', 'string', 'min:8', 'max:60'],
                'roles'          => ['required', 'array', 'min:1'],
                'roles.*'        => ['integer', 'exists:roles,id'],
                'hotel_id'       => ['nullable', 'integer', 'exists:hoteles,id'],
                'activo'         => ['required', 'boolean'],
            ];
        }

        //PATCH
        return [
            'nombre_usuario' => ['sometimes', 'string', 'max:45', Rule::unique('usuarios', 'nombre_usuario')->ignore($id)],
            'correo'         => ['sometimes', 'email', 'max:120', Rule::unique('usuarios', 'correo')->ignore($id)],
            'password'       => ['sometimes', 'string', 'min:8', 'max:60'],
            'roles'          => ['sometimes', 'array', 'min:1'],
            'roles.*'        => ['integer', 'exists:roles,id'],
            'hotel_id'       => ['sometimes', 'nullable', 'integer', 'exists:hoteles,id'],
            'activo'         => ['sometimes', 'boolean'],
        ];
    }


    public function messages(): array
    {
        return [
            'nombre_usuario.unique' => 'Ese nombre de usuario ya esta ocupado',
            'correo.email'          => 'El correo no tiene un formato valido',
            'correo.unique'         => 'Ese correo ya esta registrado',
            'password.min'          => 'La contrasena debe tener al menos 8 caracteres',
            'roles.min'             => 'Debes elegir al menos un rol',
            'roles.*.exists'        => 'Uno de los roles elegidos no existe',
        ];
    }


    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('nombreUsuario')) {
            $data['nombre_usuario'] = $this->nombreUsuario;
        }

        if ($this->has('hotelId')) {
            $data['hotel_id'] = $this->hotelId ?: null;
        }

        $this->merge($data);
    }
}
