<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pontos-chave:
 * - $fillable: habilita mass assignment somente para "nome".
 * - Relacionamento 1:N: uma Marca possui muitos Registros (hasMany).
 * - Convenções: por padrão, Eloquent espera PK "id" e timestamps "created_at/updated_at".
 *   (Se sua tabela não tiver timestamps, você pode desativar com: public $timestamps = false;)
 */

class Marcas extends Model
{
    // Define explicitamente a tabela (boa prática quando o nome foge do plural padrão)
    protected $table = 'marcas';

    // Proteção contra mass assignment – somente "nome" pode ser preenchido em create/update(array)
    protected $fillable = ['nome'];

    /*
     * 1:N — Uma Marca tem muitos Registros.
     * - FK esperada na tabela "registros": "marca_id".
     * - Assim, $marca->registros retorna uma Collection de Registros.
     */
    public function registros(){
        return $this->hasMany(Registros::class);
    }

    
}
