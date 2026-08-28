<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateMetroAguaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && ($usuario->esMaster() || $usuario->esAdministrador());
    }


    public function rules(): array
    {
        $metro = $this->route('metroAgua');

        $unico = Rule::unique('metros_agua', 'nombre')
            ->where('hotel_id', $metro->hotel_id)
            ->ignore($metro->id);

        if ($this->isMethod('put')) {
            return [
                'nombre' => ['required', 'string', 'max:45', $unico],
                'orden'  => ['required', 'integer', 'min:0', 'max:999'],
                'activo' => ['required', 'boolean'],
            ];
        }

        //PATCH
        return [
            'nombre' => ['sometimes', 'string', 'max:45', $unico],
            'orden'  => ['sometimes', 'integer', 'min:0', 'max:999'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }


    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ese hotel ya tiene un metro con ese nombre',
            'orden.integer' => 'El orden debe ser un número entero',
        ];
    }
}
