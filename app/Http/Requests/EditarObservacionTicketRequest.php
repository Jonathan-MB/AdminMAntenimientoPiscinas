<?php

namespace App\Http\Requests;

use App\Models\Rol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EditarObservacionTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && $usuario->tieneRol(Rol::MASTER, Rol::JEFE, Rol::REPARACION);
    }


    public function rules(): array
    {
        return [
            'observacion' => ['nullable', 'string', 'max:2000'],
        ];
    }


    public function messages(): array
    {
        return [
            'observacion.max' => 'La observación no puede pasar de 2000 caracteres',
        ];
    }


    protected  function prepareForValidation(): void
    {
        //  Un campo en blanco es una observacion vaciada, no la cadena vacia
        $texto = trim((string) $this->observacion);

        $this->merge([
            'observacion' => $texto === '' ? null : $texto,
        ]);
    }
}
