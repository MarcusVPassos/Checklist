<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Itens extends Model
{
    protected $table = 'itens';

    protected $fillable = ['nome'];

    // Relacionamento muitos para muitos com Registros N:N
    public function registros(){
        return $this->belongsToMany(Registros::class, 'registros_itens', 'itens_id', 'registros_id');
    }
}
