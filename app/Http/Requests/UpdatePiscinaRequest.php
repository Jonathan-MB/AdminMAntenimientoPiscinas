<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePiscinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && ($usuario->esMaster() || $usuario->esAdministrador());
    }


    public function rules(): array
    {
        $piscina = $this->route('piscina');

        $unico = Rule::unique('piscinas', 'nombre')
            ->where('hotel_id', $piscina->hotel_id)
            ->ignore($piscina->id);

        if ($this->isMethod('put')) {
            return [
                'nombre' => ['required', 'string', 'max:45', $unico],
                'orden'  => ['required', 'integer', 'min:0', 'max:999'],
                'activa' => ['required', 'boolean'],
            ];
        }

        //PATCH
        return [
            'nombre' => ['sometimes', 'string', 'max:45', $unico],
            'orden'  => ['sometimes', 'integer', 'min:0', 'max:999'],
            'activa' => ['sometimes', 'boolean'],
        ];
    }


    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ese hotel ya tiene una piscina con ese nombre',
            'orden.integer' => 'El orden debe ser un número entero',
        ];
    }
}
