<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\SoftDeletes;

trait LogsActivity
{
    // Habilita softDelete no *modelo que usar logsacvtivity*. Exige uma coluna deleted_at na tabela desse modelo.
    use SoftDeletes;

    // "Boot do trait": em Eloquent, se um traittem um método chamado boot******, o Laravel o executa automaticamento quando o modelo é inicializado 
    public static function bootLogsActivity()
    {
        // Dispara ao INSERIR um registro no banco
        static::created(function ($model){
            $model->logActivity('created');
        });

        // Dispara ao ATUALIZAR um registro existente
        static::updated(function ($model){
            $model->logActivity('updated');
        });

        // Dispara ao DELETAR, no softDelete
        static::deleted(function ($model){
            $model->logActivity ('deleted');
        });

        // Dispara ao RESTAURAR um registro deletado (SoftDelte)
        static::restored(function ($model){
            $model->logActivity('restored');
        });

        // Dispara na exclusão definitiva (forceDelete, bypass do softDelete)
        static::forceDeleted(function ($model){
            $model->logActivity('force-deleted');
        });

    }


    // Método utilitário usado por todos os handlers acima. Registra uma linha na tabela activity_logs
    public function logActivity($action)
    {
        ActivityLog::create([
            'model_type' => get_class($this), // Classe concreta do modelo (ex: App\Models\User)
            'model_id'   => $this->id, // Chave primária do registro que sofreu a ação
            'action'     => $action, // Ação textual que veio do evento
            'description'=> class_basename($this) . "{$this->name} || {$this->placa}", // Texto livre; aqui você concatena alguns campos do modelo
        ]);
    }
}

