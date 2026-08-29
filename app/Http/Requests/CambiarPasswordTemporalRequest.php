<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class CambiarPasswordTemporalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }


    public function rules(): array
    {
        //  No se pide la contraseña actual: acaba de escribirla para entrar, y
        //  justamente el problema es que se la dio otra persona
        return [
            'password' => ['required', 'string', 'min:8', 'max:60', 'confirmed'],
        ];
    }


    //  Que no vuelva a poner la misma que le dieron
    public function after(): array
    {
        return [
            function (Validator $validador) {
                $nueva = $this->input('password');

                if ($nueva && Hash::check($nueva, Auth::user()->password)) {
                    $validador->errors()->add(
                        'password',
                        'Elige una distinta de la que te dieron: esa la conoce alguien más'
                    );
                }
            },
        ];
    }


    public function messages(): array
    {
        return [
            'password.required'  => 'Escribe tu contraseña nueva',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las dos contraseñas no coinciden',
        ];
    }


    protected  function prepareForValidation(): void
    {
        $this->merge([
            'password_confirmation' => $this->passwordConfirmacion,
        ]);
    }
}
