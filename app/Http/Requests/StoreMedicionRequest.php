<?php

namespace App\Http\Requests;

use App\Models\Rol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreMedicionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = Auth::user();

        return $usuario !== null && $usuario->tieneRol(Rol::MASTER, Rol::ADMINISTRADOR, Rol::COLABORADOR);
    }


    public function rules(): array
    {
        return [
            'cl_libre'        => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'cl_total'        => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'cl_combinado'    => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'ph'              => ['nullable', 'numeric', 'min:0', 'max:14'],
            'alcalinidad'     => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'dureza_calcio'   => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'acido_cianurico' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'retrolavado'     => ['sometimes', 'boolean'],
            'observacion'     => ['nullable', 'string', 'max:255'],

            //  Cantidades por producto: la llave es el id del producto
            'dosis'           => ['nullable', 'array'],
            'dosis.*'         => ['nullable', 'numeric', 'min:0', 'max:999999'],

            //  Sin esto, un id inventado revienta contra la llave foranea
            'llavesDosis'     => ['nullable', 'array'],
            'llavesDosis.*'   => ['integer', 'exists:productos,id'],
        ];
    }


    public function messages(): array
    {
        return [
            'ph.max'               => 'El pH no puede pasar de 14',
            'ph.min'               => 'El pH no puede ser negativo',
            'dosis.*.numeric'      => 'Las cantidades de químico deben ser números',
            'dosis.*.min'          => 'Las cantidades de químico no pueden ser negativas',
            'llavesDosis.*.exists' => 'Uno de los químicos enviados no existe',
        ];
    }


    protected  function prepareForValidation(): void
    {
        //  El navegador manda camelCase; la base guarda snake_case
        $this->merge([
            'cl_libre'        => $this->vacioANulo($this->clLibre),
            'cl_total'        => $this->vacioANulo($this->clTotal),
            'cl_combinado'    => $this->vacioANulo($this->clCombinado),
            'ph'              => $this->vacioANulo($this->ph),
            'alcalinidad'     => $this->vacioANulo($this->alcalinidad),
            'dureza_calcio'   => $this->vacioANulo($this->durezaCalcio),
            'acido_cianurico' => $this->vacioANulo($this->acidoCianurico),
            'retrolavado'     => $this->boolean('retrolavado'),

            //  Las llaves del arreglo de dosis se validan como valores aparte
            'llavesDosis'     => array_keys($this->input('dosis') ?? []),
        ]);
    }


    //  Un campo en blanco es "no medido", no es cero
    private function vacioANulo($valor)
    {
        return $valor === '' || $valor === null ? null : $valor;
    }
}
