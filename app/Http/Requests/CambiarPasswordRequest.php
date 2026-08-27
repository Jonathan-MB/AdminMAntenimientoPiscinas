<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CambiarPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }


    public function rules(): array
    {
        return [
            //  current_password compara contra la contrasena real del usuario
            'password_actual' => ['required', 'current_password'],
            'password'        => ['required', 'string', 'min:8', 'max:60', 'confirmed', 'different:password_actual'],
        ];
    }


    public function messages(): array
    {
        return [
            'password_actual.required'         => 'Escribe tu contraseña actual',
            'password_actual.current_password' => 'Tu contraseña actual no es correcta',
            'password.required'                => 'Escribe la contraseña nueva',
            'password.min'                     => 'La contraseña nueva debe tener al menos 8 caracteres',
            'password.confirmed'               => 'Las dos contraseñas nuevas no coinciden',
            'password.different'               => 'La contraseña nueva debe ser distinta de la actual',
        ];
    }


    protected  function prepareForValidation(): void
    {
        $this->merge([
            'password_actual'       => $this->passwordActual,
            'password_confirmation' => $this->passwordConfirmacion,
        ]);
    }
}
