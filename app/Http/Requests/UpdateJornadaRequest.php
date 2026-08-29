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
            'materiales_sacados' => ['sometimes', 'nullable', 'string', 'max:2000'],

            //  Una lectura por metro: la llave es el id del metro
            'lecturas'           => ['sometimes', 'nullable', 'array'],
            'lecturas.*'         => ['nullable', 'numeric', 'min:0', 'max:9999999999'],

            //  Sin esto, un id inventado revienta contra la llave foranea
            'llavesLecturas'     => ['sometimes', 'nullable', 'array'],
            'llavesLecturas.*'   => ['integer', 'exists:metros_agua,id'],
        ];
    }


    public function messages(): array
    {
        return [
            'lecturas.*.numeric'        => 'La lectura del metro de agua debe ser un número',
            'lecturas.*.min'            => 'La lectura del metro de agua no puede ser negativa',
            'llavesLecturas.*.exists'   => 'Uno de los metros de agua enviados no existe',
            'materiales_sacados.max'    => 'El texto de materiales sacados es demasiado largo',
        ];
    }


    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('materialesSacados')) {
            $data['materiales_sacados'] = $this->materialesSacados === '' ? null : $this->materialesSacados;
        }

        if ($this->has('lecturas')) {
            $data['llavesLecturas'] = array_keys($this->input('lecturas') ?? []);
        }

        $this->merge($data);
    }
}
