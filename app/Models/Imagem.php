<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Pontos-chave:
 * - $fillable: permite atribuição em massa dos campos gravados no upload.
 * - 1:N (belongsTo): cada Imagem pertence a um Registro.
 * - Convenção: FK "registro_id" fica na própria tabela "imagens".
 */

class Imagem extends Model
{
    // Quais atributos podem ser preenchidos em massa
    protected $fillable = ['registro_id', 'path', 'posicao'];

    // Nome da tabela (explícito, por clareza)
    protected $table = 'imagens';

    /**
     * N:1 — Esta imagem pertence a um Registro.
     * - FK local: "registro_id"
     * - Assim, $imagem->registro retorna o Model Registros ao qual ela pertence.
     */
    public function registro(){
        return $this->belongsTo(Registros::class, 'registro_id');
    }
}
