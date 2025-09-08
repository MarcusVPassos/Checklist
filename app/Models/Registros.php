<?php

namespace App\Models;

use Creagia\LaravelSignPad\Concerns\RequiresSignature;
use Creagia\LaravelSignPad\Contracts\CanBeSigned;
use Creagia\LaravelSignPad\Contracts\ShouldGenerateSignatureDocument;
use Creagia\LaravelSignPad\SignatureDocumentTemplate;
use Creagia\LaravelSignPad\SignaturePosition;
use Creagia\LaravelSignPad\Templates\BladeDocumentTemplate;
use Illuminate\Database\Eloquent\Model;

class Registros extends Model implements CanBeSigned, ShouldGenerateSignatureDocument 
{
    use RequiresSignature;

    protected $table = 'registros';

    protected $fillable = 
    [
        'tipo', 'placa', 'marca_id', 'no_patio', 'modelo', 'observacao', 'reboque_condutor', 'reboque_placa', 'assinatura_path',
    ];

    protected $casts = 
    [
        'no_patio' => 'boolean',
    ];

    public function getSignatureDocumentTemplate(): SignatureDocumentTemplate
    {
        return new SignatureDocumentTemplate(
            outputPdfPrefix: 'registro', // prefixo do arquivo PDF gerado
            template: new BladeDocumentTemplate('pdf/my-pdf-blade-template'),
            signaturePositions: [
                new SignaturePosition(signaturePage: 1, signatureX: 20, signatureY: 25),
                // adicione outras posições se quiser
            ],
        );
    }

    // Relacionamento muitos para muitos com Itens N:N
    public function itens(){
        return $this->belongsToMany(Itens::class, 'registros_itens', 'registros_id', 'itens_id');
    }

    // 1:N (lado N) — cada registro pertence a UMA marca
    public function marca(){
        return $this->belongsTo(Marcas::class, 'marca_id');  //O Eloquent deriva o nome da FK do nome do método + _id
    }

    // 1:N - Um registro tem muitas imagens
    public function imagens(){
        return $this->hasMany(Imagem::class, 'registro_id');
    }
}
