<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Pontos-chave:
 * - $fillable: habilita mass assignment para "nome".
 * - N:N (belongsToMany) com Registros, via tabela pivô "registros_itens".
 * - As colunas da pivô foram definidas explicitamente: 'itens_id' e 'registros_id'
 *   (porque não seguimos o padrão "item_id" / "registro_id").
 */

class Itens extends Model
{
    protected $table = 'itens';

    protected $fillable = ['nome'];

    /**
     * N:N — Muitos Itens pertencem a Muitos Registros.
     * - Tabela pivô: registros_itens
     * - Coluna da FK para este model na pivô: 'itens_id'
     * - Coluna da FK para Registros na pivô: 'registros_id'
     * - Assim, $item->registros retorna uma Collection de Registros relacionados.
     */
    public function registros(){
        return $this->belongsToMany(
            Registros::class,    // model relacionado
            'registros_itens',   // tabela pivô
            'itens_id',          // FK deste model na pivô
            'registros_id'       // FK do model relacionado na pivô
        );
    }
}
