<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/*
 * FormRequest = validação desacoplada do controller:
 * - O Laravel chama ->rules() antes de executar o método update().
 * - Se falhar, ele redireciona de volta com errors/old() automaticamente.
 */

class RegistroUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // depois pode integrar com policies/permissions
    }

    public function rules(): array
    {
        // pegue o modelo da rota para usar no ignore do unique
        $registro = $this->route('registro'); // Registros $registro no controller

        // Mesmas listas do Store
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

        $opcionais = [
            'bateria_carro',
            'chave_carro',
            'estepe_do_veiculo',
            'chave_moto',
            'bateria_moto',
        ];

        // Regra base de imagens
        $imgRule = ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'];

        $rules = [
            'tipo'              => ['required', 'in:carro,moto'],
            'placa'             => [
                'required',
                'string',
                'max:10',
                // <- diferença para UPDATE: ignora o próprio id
                Rule::unique('registros', 'placa')->ignore($registro->id),
            ],
            'marca_id'          => ['required', 'exists:marcas,id'],
            'modelo'            => ['required', 'string', 'max:120'],
            'no_patio'          => ['boolean'],
            'observacao'        => ['nullable', 'string'],
            'reboque_condutor'  => ['required', 'string', 'max:120'],
            'reboque_placa'     => ['required', 'string', 'max:10'],

            'itens'   => ['nullable', 'array'],
            'itens.*' => ['integer', 'exists:itens,id'],

            /*
             * ⚠️ Ponto-chave que corrigiu seu problema:
             * 'sometimes|nullable' em 'assinatura_b64'.
             * - 'sometimes': só valida se o campo existir na requisição.
             * - 'nullable': se vier presente porém null/vazio (ConvertEmptyStringsToNull),
             *               as outras regras são ignoradas.
             * Assim, não assinar num update NÃO quebra a validação.
             */
            'assinatura_b64' => [
                'sometimes',
                'nullable',
                'string',
                'regex:/^data:image\\/(png|jpe?g|webp);base64,/i',
            ],

            // caso aceite upload direto também na edição (opcional):
            // 'assinatura' => ['sometimes','file','image','mimes:jpg,jpeg,png,webp','max:8192'],

            // novas imagens (opcionais) no update
            'imagens'    => ['sometimes', 'array'],
            'imagens.*'  => $imgRule,

            // ids de imagens a remover
            'remove_imagens'   => ['sometimes', 'array'],
            'remove_imagens.*' => ['integer'],
        ];

        /**
         * 📌 Escolha UMA das abordagens abaixo para as fotos "obrigatórias por tipo":
         *
         * A) PRÁTICA (recomendada na edição):
         *    - Não exigir reenvio das obrigatórias.
         *    - Só validar se vierem (sometimes).
         */
        foreach (array_unique(array_merge($carroObrig, $motoObrig, $opcionais)) as $pos) {
            $rules[$pos] = array_merge(['sometimes'], $imgRule);
        }

        /**
         * B) RÍGIDA (se você quiser forçar reenvio no update):
         *    Descomente abaixo e remova o loop acima:
         *
         * foreach ($carroObrig as $pos) {
         *     $rules[$pos] = array_merge(['required_if:tipo,carro'], $imgRule);
         * }
         * foreach ($motoObrig as $pos) {
         *     $rules[$pos] = array_merge(['required_if:tipo,moto'], $imgRule);
         * }
         * foreach ($opcionais as $pos) {
         *     $rules[$pos] = array_merge(['nullable'], $imgRule);
         * }
         */

        return $rules;
    }

    public function messages(): array
    {
        return [
            'placa.unique' => 'Esta placa já está cadastrada.',
        ];
    }
}
