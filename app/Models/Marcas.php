<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marcas extends Model
{
    // 1:N Marca tem muitos registros
    public function registros(){
        return $this->hasMany(Registros::class);
    }
}
