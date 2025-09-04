<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistroStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Depois vem com as permissions
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'in:carro,moto'],
            'placa' => ['required', 'string', 'max:10','unique:registros,placa'],
            'marca_id' => ['required', 'exists:marcas,id'],
            'modelo' => ['required', 'string', 'max:120'],
            'no_patio' => ['boolean'],  // se não vier dados, usa true
            'observacao' => ['nullable', 'string'],  // se não vier dados, usa null
            'reboque_condutor'=>['required', 'string', 'max:120'],
            'reboque_placa'=>['required', 'string', 'max:10'],
            'itens'       => ['nullable','array'],
            'itens.*'     => ['integer','exists:itens,id'],  // .* -> Válida cada indice desse array com estas regras.
            'fotos'       => ['required', 'array'],
            'fotos.*'     => ['file','mimes:jpg,jpeg,png,webp','max:4096'],
            'assinatura'  => ['required','file','mimes:png,jpg,jpeg','max:2048'],
        ];
    }
}

// .* é o atalho do Laravel para validar cada valor de um array
