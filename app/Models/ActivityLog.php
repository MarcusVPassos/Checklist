<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    // se usar fillable:
    protected $fillable = [
        'model_type', 'model_id', 'action', 'user_id', 'description', 'changes',
    ];

    // ler/gravar JSON como array + datas como Carbon
    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quem causou o log (coluna user_id na sua migration).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Alvo do log (polimórfico manual usando model_type/model_id).
     * Assim você pode fazer $log->model e obter o registro (User, Registro, etc.).
     */
    public function model(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }
}
