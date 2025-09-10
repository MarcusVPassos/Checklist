<?php

namespace App\Models;

use Creagia\LaravelSignPad\Concerns\RequiresSignature;
use Creagia\LaravelSignPad\Contracts\CanBeSigned;
use Creagia\LaravelSignPad\Contracts\ShouldGenerateSignatureDocument;
use Creagia\LaravelSignPad\SignatureDocumentTemplate;
use Creagia\LaravelSignPad\SignaturePosition;
use Creagia\LaravelSignPad\Templates\BladeDocumentTemplate;
use Illuminate\Database\Eloquent\Model;

/**
 * - $table: mapeia explicitamente a tabela "registros".
 * - $fillable: libera mass assignment somente para campos permitidos.
 * - $casts: tipa automaticamente atributos (no_patio -> boolean).
 * - Relacionamentos: belongsTo (marca), hasMany (imagens), belongsToMany (itens).
 * - Integração com o Laravel Sign Pad (Creagia): implementa contratos para
 *   exigir assinatura e gerar documento (PDF) usando Blade como template.
 */

class Registros extends Model implements CanBeSigned, ShouldGenerateSignatureDocument 
{
    /**
     * Trait do pacote Creagia que marca este model como "requer assinatura".
     * Ele habilita fluxos de assinatura/documento conforme a lib.
     */
    use RequiresSignature;

    /*
     * Tabela explicitamente definida (boa prática quando o nome não é o padrão plural de "Registro").
     */
    protected $table = 'registros';

    /**
     * Mass Assignment: somente estes campos podem ser atribuídos em massa (create/update).
     * Evita vulnerabilidades quando recebemos arrays do request.
     * Docs: Eloquent Mass Assignment.
     */
    protected $fillable = 
    [
        'tipo', 'placa', 'marca_id', 'no_patio', 'modelo', 'observacao', 'reboque_condutor', 'reboque_placa', 'assinatura_path',
    ];

    /*
     * Casts: converte automaticamente tipos ao ler/gravar.
     * Aqui garantimos que no_patio sempre seja boolean no PHP/JSON.
     * Docs: Attribute Casting.
     */
    protected $casts = 
    [
        'no_patio' => 'boolean',
    ];

    /*
     * Implementação do contrato ShouldGenerateSignatureDocument (Creagia):
     * Define como será gerado o PDF de assinatura deste registro.
     * - outputPdfPrefix: prefixo do nome do PDF gerado.
     * - template: qual Blade será renderizado para construir o PDF.
     * - signaturePositions: coordenadas/posições onde a(s) assinatura(s) será(ão) aplicada(s).
     *
     * Observação: As coordenadas (X/Y/página) dependem do template do PDF.
     * Ajuste conforme seu layout.
     */
    public function getSignatureDocumentTemplate(): SignatureDocumentTemplate
    {
        return new SignatureDocumentTemplate(
            outputPdfPrefix: 'registro', // prefixo do arquivo PDF gerado
            template: new BladeDocumentTemplate('pdf/my-pdf-blade-template'),
            signaturePositions: [
                // Posição 1: página 1 nas coordenadas (20, 25).
                // Você pode adicionar mais SignaturePosition para múltiplas assinaturas.
                new SignaturePosition(signaturePage: 1, signatureX: 20, signatureY: 25),
                // adicione outras posições se quiser
            ],
        );
    }

    /*
     * Relacionamento N:N (belongsToMany) com Itens.
     * - Tabela pivô: registros_itens.
     * - Chaves da pivô: 'registros_id' e 'itens_id'.
     * Importante: definimos explicitamente os nomes das FKs da pivô
     * porque a tabela/colunas não seguem o padrão "registro_id" / "item_id".
     */
    public function itens(){
        return $this->belongsToMany(
            Itens::class,        // Model relacionado
            'registros_itens',   // Tabela pivô
            'registros_id',      // FK deste model na pivô
            'itens_id'           // FK do model relacionado na pivô
        );
    }

    /*
     * Relacionamento N:1 — cada Registro pertence a UMA Marca.
     * - A FK local é "marca_id" (coluna existente em 'registros').
     * - Definimos explicitamente para clareza, embora Eloquent inferisse pelo nome.
     */
    public function marca(){
        return $this->belongsTo(Marcas::class, 'marca_id');  //O Eloquent deriva o nome da FK do nome do método + _id
    }

    /*
     * Relacionamento 1:N — um Registro possui muitas Imagens.
     * - A FK nas imagens é "registro_id".
     * - Com isso, $registro->imagens retorna a coleção (Illuminate\Database\Eloquent\Collection).
     */
    public function imagens(){
        return $this->hasMany(Imagem::class, 'registro_id');
    }
}
