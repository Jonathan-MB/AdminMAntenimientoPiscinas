<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IniciarSesionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'nombre_usuario' => ['required', 'string', 'max:45'],
            'password'       => ['required', 'string'],
        ];
    }


    public function messages(): array
    {
        return [
            'nombre_usuario.required' => 'Escribe tu nombre de usuario',
            'nombre_usuario.max'      => 'El nombre de usuario no puede pasar de 45 caracteres',
            'password.required'       => 'Escribe tu contrasena',
        ];
    }


    protected  function prepareForValidation(): void
    {
        $this->merge([
            'nombre_usuario' => $this->nombreUsuario,
        ]);
    }
}
