<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imagem extends Model
{
    protected $table = 'imagens';

    // 1:N Imagem pertence a um registro
    public function registro(){
        return $this->belongsTo(Registros::class, 'registro_id');
    }
}
