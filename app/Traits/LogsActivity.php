<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Models\Registros;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    // Habilita softDelete no *modelo que usar logsacvtivity*. Exige uma coluna deleted_at na tabela desse modelo.
    use SoftDeletes;

    // "Boot do trait": em Eloquent, se um traittem um método chamado boot******, o Laravel o executa automaticamento quando o modelo é inicializado 
    public static function bootLogsActivity()
    {
        // Dispara ao INSERIR um registro no banco
        static::created(function ($model) {
            $model->logActivity('created');
        });

        // Dispara ao ATUALIZAR um registro existente
        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        // Dispara ao DELETAR, no softDelete
        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });

        // Dispara ao RESTAURAR um registro deletado (SoftDelte)
        static::restored(function ($model) {
            $model->logActivity('restored');
        });

        // Dispara na exclusão definitiva (forceDelete, bypass do softDelete)
        static::forceDeleted(function ($model) {
            $model->logActivity('force-deleted');
        });
    }

    private function resolveActor(): ?User
    {
        if ($u = Auth::user()) {
            return $u;
        }
        foreach (array_keys(config('auth.guards', [])) as $guard) {
            if ($u = Auth::guard($guard)->user()) {
                return $u;
            }
        }
        return null;
    }

    // $meta permite enviar placa, role, target, etc.
    // ... topo igual

    public function logActivity(string $action, array $meta = []): void
    {
        $actor = $this->resolveActor();

        $changes = array_merge([
            'actor_id'   => $actor?->id,
            'actor_name' => $actor?->name ?? 'Sistema',
        ], $meta);

        // ✅ Sempre tente preencher o alvo se não veio no $meta
        if (empty($changes['target_id'])) {
            $changes['target_id'] = method_exists($this, 'getKey') ? $this->getKey() : null;
        }
        if (empty($changes['target_name'])) {
            if (isset($this->name) && $this->name !== '') {
                $changes['target_name'] = $this->name;          // User, etc.
            } elseif ($this instanceof Registros && isset($this->placa)) {
                $changes['target_name'] = $this->placa;         // fallback amigável
            }
        }

        // Mantém placa para Registros
        if ($this instanceof Registros && !isset($changes['placa']) && isset($this->placa)) {
            $changes['placa'] = $this->placa;
        }

        ActivityLog::create([
            'model_type'  => get_class($this),
            'model_id'    => $this->getKey(),
            'action'      => $action,
            'user_id'     => $actor?->id,
            'description' => null,
            'changes'     => $changes,
        ]);
    }
}
