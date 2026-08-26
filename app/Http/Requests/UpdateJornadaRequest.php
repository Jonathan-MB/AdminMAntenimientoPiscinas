<?php

namespace App\Http\Requests;

use App\Models\Rol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateJornadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && $usuario->tieneRol(Rol::MASTER, Rol::ADMINISTRADOR, Rol::COLABORADOR);
    }


    public function rules(): array
    {
        return [
            'lectura_metro_agua' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999'],
            'entregada'          => ['sometimes', 'boolean'],
        ];
    }


    public function messages(): array
    {
        return [
            'lectura_metro_agua.numeric' => 'La lectura del metro de agua debe ser un número',
            'lectura_metro_agua.min'     => 'La lectura del metro de agua no puede ser negativa',
        ];
    }


    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('lecturaMetroAgua')) {
            $data['lectura_metro_agua'] = $this->lecturaMetroAgua === '' ? null : $this->lecturaMetroAgua;
        }

        $this->merge($data);
    }
}
