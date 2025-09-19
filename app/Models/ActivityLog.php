<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Registros;

class ActivityLog extends Model
{
    // se usar fillable:
    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'user_id',
        'description',
        'changes',
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
        return $this->belongsTo(User::class);
    }

    /**
     * Alvo do log (polimórfico manual usando model_type/model_id).
     * Assim você pode fazer $log->model e obter o registro (User, Registro, etc.).
     */
    public function model(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }

    public function getActorNameAttribute(): string
    {
        return $this->user?->name ?? ($this->changes['actor_name'] ?? 'Sistema');
    }


    // ===== Texto amigável para a tabela =====

    // App\Models\ActivityLog.php

    public function getHumanDescriptionAttribute(): string
    {
        $c    = $this->changes ?? [];

        $ator = $this->actor_name;
        $alvo = $c['target_name'] ?? $this->model?->name ?? '—';

        // pega o nome da permissão em qualquer chave/forma comum
        $perm = $c['permission']
            ?? $c['permission_name']
            ?? ($c['permissions'][0] ?? null)
            ?? ($c['perm'] ?? null);

        $role = $c['role'] ?? null;

        if ($this->model_type === \App\Models\User::class) {

            // Ações de permissão: sempre mostrar o nome se existir
            if (str_starts_with($this->action, 'permission-')) {
                $concedeu = $this->action === 'permission-attached';
                $verbo = $concedeu ? 'concedeu' : 'revogou';
                $prep  = $concedeu ? 'para'     : 'de';

                return $perm
                    ? "$ator $verbo a permissão {$perm} $prep $alvo"
                    : "$ator $verbo uma permissão $prep $alvo";
            }

            // Ações de papel
            if (str_starts_with($this->action, 'role-')) {
                $deu    = $this->action === 'role-attached';
                $verbo  = $deu ? 'deu acesso' : 'removeu o acesso';
                $prep   = $deu ? 'para'       : 'de';

                return $role
                    ? "$ator $verbo {$role} $prep $alvo"
                    : "$ator $verbo $prep $alvo";
            }

            // CRUD do usuário
            return match ($this->action) {
                'created'  => "$ator criou o usuário $alvo",
                'updated'  => "$ator atualizou o usuário $alvo",
                'deleted'  => "$ator excluiu o usuário $alvo",
                'restored' => "$ator restaurou o usuário $alvo",
                default    => $this->description ?: "{$this->action} em $alvo",
            };
        }

        if ($this->model_type === \App\Models\Registros::class) {
            $placa = $c['placa'] ?? $this->model?->placa ?? null;
            $tag   = $placa ? "[$placa]" : '';

            return match ($this->action) {
                'created'       => "$ator criou Registro $tag",
                'updated'       => "$ator atualizou Registro $tag",
                'deleted'       => "$ator excluiu Registro $tag",
                'restored'      => "$ator restaurou Registro $tag",
                'force-deleted' => "$ator removeu definitivamente Registro $tag",
                default         => $this->description ?: "{$this->action} em Registro $tag",
            };
        }

        return $this->description ?: "{$this->action}";
    }
}
