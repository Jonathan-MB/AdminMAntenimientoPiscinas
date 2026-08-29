<?php

namespace App\Http\Requests;

use App\Models\FotoTicket;
use App\Models\Rol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreFotoTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && $usuario->tieneRol(Rol::MASTER, Rol::JEFE, Rol::REPARACION);
    }


    public function rules(): array
    {
        return [
            'fotos'   => ['required', 'array', 'max:' . FotoTicket::MAXIMO_POR_TICKET],
            'fotos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:' . FotoTicket::MAXIMO_KB],
        ];
    }


    public function messages(): array
    {
        return [
            'fotos.required' => 'Elige al menos una foto',
            'fotos.max'      => 'Como máximo ' . FotoTicket::MAXIMO_POR_TICKET . ' fotos a la vez',
            'fotos.*.mimes'  => 'Solo se aceptan fotos JPG, PNG o WEBP. Si tu celular guarda en HEIC, cámbialo a «Más compatible» o toma la foto desde el navegador',
            'fotos.*.max'    => 'Cada foto debe pesar menos de ' . (FotoTicket::MAXIMO_KB / 1024) . ' MB',
        ];
    }
}
