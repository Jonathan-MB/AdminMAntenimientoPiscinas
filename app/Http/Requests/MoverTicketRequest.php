<?php

namespace App\Http\Requests;

use App\Models\Rol;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MoverTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && $usuario->tieneRol(Rol::MASTER, Rol::JEFE, Rol::REPARACION);
    }


    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(array_keys(Ticket::estados()))],
        ];
    }


    public function messages(): array
    {
        return [
            'estado.required' => 'Indica el estado nuevo',
            'estado.in'       => 'Ese estado no existe',
        ];
    }
}
