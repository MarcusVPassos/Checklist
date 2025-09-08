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
        // posições por tipo
        $carroObrig = [
            'frente',
            'lado_direito',
            'lado_esquerdo',
            'traseira',
            'capo_aberto',
            'numero_do_motor',
            'painel_lado_direito',
            'painel_lado_esquerdo',
        ];

        $motoObrig = [
            'frente',
            'lado_direito',
            'lado_esquerdo',
            'traseira',
            'motor_lado_direito',
            'motor_lado_esquerdo',
            'painel_moto',
        ];

        $imgRule = ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192']; // Regra base para todas imagens

        $rules = [
            'tipo' => ['required', 'in:carro,moto'],
            'placa' => ['required', 'string', 'max:10', 'unique:registros,placa'],
            'marca_id' => ['required', 'exists:marcas,id'],
            'modelo' => ['required', 'string', 'max:120'],
            'no_patio' => ['boolean'],  // se não vier dados, usa true
            'observacao' => ['nullable', 'string'],  // se não vier dados, usa null
            'reboque_condutor' => ['required', 'string', 'max:120'],
            'reboque_placa' => ['required', 'string', 'max:10'],
            'itens'       => ['nullable', 'array'],
            'itens.*'     => ['integer', 'exists:itens,id'],  // .* -> Válida cada indice desse array com estas regras.

            // 'fotos'       => ['required', 'array'],
            // 'fotos.*'     => ['file','mimes:jpg,jpeg,png,webp','max:4096'],

            // 'assinatura'  => array_merge(['required', 'file'], $imgRule),
            'assinatura_b64' => [
                'required',
                'string',
                // garante que venha algo como data:image/png;base64,AAAA...
                'regex:/^data:image\\/(png|jpe?g|webp);base64,/i',
            ],
        ];

        // posições de CARRO (obrigatórias quando tipo = carro)
        foreach ($carroObrig as $pos) {
            $rules[$pos] = array_merge(['required_if:tipo,carro'], $imgRule);
        }

        // posições de MOTO (obrigatórias quando tipo = moto)
        foreach ($motoObrig as $pos) {
            $rules[$pos] = array_merge(['required_if:tipo,moto'], $imgRule);
        }

        // posições opcionais (podem existir para ambos os tipos)
        $opcionais = [
            'bateria_carro',
            'chave_carro',
            'estepe_do_veiculo',
            'chave_moto',
            'bateria_moto',
        ];

        foreach ($opcionais as $pos) {
            $rules[$pos] = array_merge(['nullable'], $imgRule);
        }

        return $rules;
    }
}

// .* é o atalho do Laravel para validar cada valor de um array
