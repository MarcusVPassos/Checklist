<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest responsável por validar os dados
 * no momento da criação de um Registro.
 *
 * Vantagens de usar FormRequest:
 * - Centraliza as regras de validação fora do controller.
 * - Facilita manutenção e testes.
 * - Garante que só dados válidos chegam no método store().
 */
class RegistroStoreRequest extends FormRequest
{
    /**
     * Define se o usuário está autorizado a usar esse request.
     * Aqui deixamos true (qualquer usuário autenticado pode).
     * Em projetos mais avançados, integramos com políticas/permissions.
     */
    public function authorize(): bool
    {
        return true; // Depois vem com as permissions
    }


    /**
     * Define as regras de validação para cada campo enviado.
     * Docs: https://laravel.com/docs/12.x/validation
     */
    public function rules(): array
    {
        /**
         * Listas de posições obrigatórias dependendo do tipo (carro/moto).
         * Isso simplifica a lógica em vez de escrever várias regras duplicadas.
         */
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

        /**
         * Regra base para todas as imagens:
         * - type: image
         * - mimes: apenas jpg/jpeg/png/webp
         * - max: até 8 MB
         */
        $imgRule = ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192']; // Regra base para todas imagens

        /**
         * Regras iniciais (comuns a todos os tipos).
         * - required: campo obrigatório
         * - in: restringe valores possíveis
         * - unique: garante que a placa não se repita
         * - exists: valida se ID existe em outra tabela
         * - nullable: aceita vazio/null
         * - array / itens.*: garante que o array é válido e cada índice aponta para IDs válidos
         * - regex: garante formato específico (no caso, assinatura Base64).
         */
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

            // 'assinatura'  => array_merge(['required', 'file'], $imgRule),
            'assinatura_b64' => [
                'required',
                'string',
                // garante que venha algo como data:image/png;base64,AAAA...
                'regex:/^data:image\\/(png|jpe?g|webp);base64,/i',
            ],
            'user_id' => 'prohibited', // cliente não manda o user_id
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

/**
 * Extra:
 * - O sufixo ".*" em itens.* significa "valide cada índice do array itens".
 *   Exemplo:
 *   itens = [1, 2, 3]
 *   → valida que cada um é integer e existe na tabela itens.
 *
 * - required_if é o que garante diferenciação:
 *   se tipo = carro → exige fotos de carro
 *   se tipo = moto  → exige fotos de moto
 */
