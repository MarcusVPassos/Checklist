<?php

namespace App\Models;

use Creagia\LaravelSignPad\Concerns\RequiresSignature;
use Creagia\LaravelSignPad\Contracts\CanBeSigned;
use Creagia\LaravelSignPad\Contracts\ShouldGenerateSignatureDocument;
use Creagia\LaravelSignPad\SignatureDocumentTemplate;
use Creagia\LaravelSignPad\SignaturePosition;
use Creagia\LaravelSignPad\Templates\BladeDocumentTemplate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

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

    use HasFactory, LogsActivity;
    // Para o delete ser mudança de status em vez de delete abrupto  yes baby
    use SoftDeletes;
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
    protected $fillable = // Autoriza o update e create a receber esse campo.
    [
        'user_id',
        'tipo',
        'placa',
        'marca_id',
        'no_patio',
        'modelo',
        'observacao',
        'reboque_condutor',
        'reboque_placa',
        'assinatura_path',
    ];

    /*
     * Casts: converte automaticamente tipos ao ler/gravar.
     * Aqui garantimos que no_patio sempre seja boolean no PHP/JSON.
     * Docs: Attribute Casting.
     */
    protected $casts =
    [
        'no_patio' => 'boolean', // transforma 0/1 em false/true. Assim na view pode usar como um boolean real em PHP.
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
    public function itens()
    {
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
    public function marca()
    {
        return $this->belongsTo(Marcas::class, 'marca_id');  //O Eloquent deriva o nome da FK do nome do método + _id
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // mesma coisa do marca
    }

    /*
     * Relacionamento 1:N — um Registro possui muitas Imagens.
     * - A FK nas imagens é "registro_id".
     * - Com isso, $registro->imagens retorna a coleção (Illuminate\Database\Eloquent\Collection).
     */
    public function imagens()
    {
        return $this->hasMany(Imagem::class, 'registro_id');
    }

    // SCOPES
    // em Local Scopes do Eloquent, qualquer método do model que começa com scopeXxx vira um filtro encadeável como ->xxx() na query
    public function scopePlaca($q, ?string $placa) // ?string pode ser string ou null, sem medo se vier null o scope não aplica filtro.
    {  // $q é o query builder do Eloquent
        // toda vez que chama ->where(...), é adicionada uma condiação à query e o builder é retornado para continuar encadeado
        return $placa ? $q->where('placa', 'like', "%{$placa}%") : $q; // se tem valor filtra se não devolve a query intacta
        // like é o opserador SQL para busca aproximada. % é cúringa
    }

    public function scopeUser($q, $userId){
        return $userId ? $q->where('user_id', $userId) : $q;
    }

    public function scopeMarca($q, $marcaId)
    {
        return $marcaId ? $q->where('marca_id', $marcaId) : $q;
    }

    public function scopeItem($q, $itemId)
    {
        return $itemId
            ? $q->whereHas('itens', fn($rel) => $rel->where('itens.id', $itemId))
            : $q;
    }

    public function scopeModelo($q, ?string $modelo)
    {
        return $modelo ? $q->where('modelo', 'like', "%{$modelo}%") : $q;
    }

    public function scopePeriodo($q, ?string $from, ?string $to)
    {
        if ($from) $q->whereDate('created_at', '>=', $from);
        if ($to) $q->whereDate('created_at', '<=', $to);
        return $q;
    }

    public function scopeMesAno($q, $mes, $ano)
    {
        if ($mes) $q->whereMonth('created_at', $mes);
        if ($ano) $q->whereYear('created_at', $ano);
        return $q;
    }

    public function scopeTipo($q, ?string $tipo)
    {
        return $tipo ? $q->where('tipo', $tipo) : $q;
    }

    public function scopeStatusPatio($q, ?string $status)
    {
        //status: "no_patio" | "saiu"
        if ($status === 'no_patio') return $q->where('no_patio', true);
        if ($status === 'saiu') return $q->where('no_patio', false);
        return $q;
    }
}
