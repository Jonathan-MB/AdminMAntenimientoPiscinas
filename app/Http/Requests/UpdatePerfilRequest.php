<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }


    public function rules(): array
    {
        return [
            'correo' => ['required', 'email', 'max:120', Rule::unique('usuarios', 'correo')->ignore(Auth::id())],
        ];
    }


    public function messages(): array
    {
        return [
            'correo.required' => 'El correo es obligatorio',
            'correo.email'    => 'El correo no tiene un formato válido',
            'correo.unique'   => 'Ese correo ya está registrado por otro usuario',
        ];
    }
}
