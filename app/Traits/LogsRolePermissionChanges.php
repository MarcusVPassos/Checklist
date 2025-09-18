<?php

namespace App\Traits;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;

trait LogsRolePermissionChanges
{
    // Importa os dois traits da Spatie e resolve conflitos de método
    use HasRoles, HasPermissions {
        // ROLES (Metódos só existem em HasRoles)
        HasRoles::assignRole as protected spatieAssignRole; // cria alias para o original
        HasRoles::removeRole as protected spatieRemoveRole; // ""
        HasRoles::syncRoles  as protected spatieSyncRoles; // ""

        // ======   PERMISSIONS (resolver conflitos)    =======
        // Diz ao PHP: quando houver "givePermissionTo/revokePermissionTo/syncPermissions", use a versão do HasPermissions ( e Não do HasRoles).
        HasPermissions::givePermissionTo    insteadof HasRoles;
        HasPermissions::revokePermissionTo  insteadof HasRoles;
        HasPermissions::syncPermissions     insteadof HasRoles;

        // E ainda criamos aliases para conseguir chamar o original por dentro do wrapper.
        HasPermissions::givePermissionTo    as protected spatieGivePermissionTo;
        HasPermissions::revokePermissionTo  as protected spatieRevokePermissionTo;
        HasPermissions::syncPermissions     as protected spatieSyncPermissions;
    }

    /** Flags para suprimir logs quando assign/give forem chamados por sync */
    protected bool $suppressRoleLog = false;
    protected bool $suppressPermLog = false;

    /** Helper: recebe vários omes (roles/perms) e grava um log por nome */
    protected function logMany(string $action, array $names): void
    {
        foreach ($names as $name) {
            // espera-se que o Model tenha um método LogActivity ($string)
            $this->logActivity("{$action}: {$name}");
        }
    }

    /* ===================== ROLES ===================== */

    //Wrapper de assignRole, chama o método original via alias e depois loga
    public function assignRole(...$roles) // parâmetro variádico: aceita 1..n argumentos
    {
        $res = $this->spatieAssignRole(...$roles);

        // Só loga se NÃO estivermos dentro de um syncRoles() (evita duplicidaade)
        if (!$this->suppressRoleLog) {
            $this->logActivity('role-attached');
        }

        return $res; // mantém o contrato original 
    }

    // Wrapper de removeRole: mesmo raciocínio do assignRole
    public function removeRole(...$roles)
    {
        $res = $this->spatieRemoveRole(...$roles);

        if (!$this->suppressRoleLog) {
            $this->logActivity('role-detached');
        }

        return $res;
    }

    // Wrapper de syncRoles: calcula o delta (antes/depois) e loga granularmente
    public function syncRoles(...$roles)
    {
        // “Antes” direto do banco (sem cache de relação)
        $beforeIds = $this->roles()->pluck('id')->all();

        // Suprime logs de assignRole/removeRole disparados internamente
        $this->suppressRoleLog = true;
        $res = $this->spatieSyncRoles(...$roles);
        $this->suppressRoleLog = false;

        // Captura o "estado depois"
        $afterIds = $this->roles()->pluck('id')->all();

        // Quais IDs entraram e saíram?
        $addedIds   = array_values(array_diff($afterIds, $beforeIds));
        $removedIds = array_values(array_diff($beforeIds, $afterIds));

        // Transforma IDs em nomes (para uma descrição de log mais útil)
        $addedNames   = $addedIds   ? Role::whereIn('id', $addedIds)->pluck('name')->all() : [];
        $removedNames = $removedIds ? Role::whereIn('id', $removedIds)->pluck('name')->all() : [];

        // Grava Logs "role-attached: {nome}" para cada nome adicionado ZZ "role-detached: ""
        $this->logMany('role-attached',  $addedNames);
        $this->logMany('role-detached',  $removedNames);

        return $res;
    }

    /* ================= PERMISSIONS =================== */

    // Wrapper de givePermissionTo
    public function givePermissionTo(...$permissions)
    {
        $res = $this->spatieGivePermissionTo(...$permissions);

        if (!$this->suppressPermLog) {
            $this->logActivity('permission-attached');
        }

        return $res;
    }

    public function revokePermissionTo(...$permissions)
    {
        $res = $this->spatieRevokePermissionTo(...$permissions);

        if (!$this->suppressPermLog) {
            $this->logActivity('permission-detached');
        }

        return $res;
    }

    public function syncPermissions(...$permissions)
    {
        $beforeIds = $this->permissions()->pluck('id')->all();

        // Suprime logs de give/revoke disparados internamente
        $this->suppressPermLog = true;
        $res = $this->spatieSyncPermissions(...$permissions);
        $this->suppressPermLog = false;

        $afterIds = $this->permissions()->pluck('id')->all();

        $addedIds   = array_values(array_diff($afterIds, $beforeIds));
        $removedIds = array_values(array_diff($beforeIds, $afterIds));

        $addedNames   = $addedIds   ? Permission::whereIn('id', $addedIds)->pluck('name')->all() : [];
        $removedNames = $removedIds ? Permission::whereIn('id', $removedIds)->pluck('name')->all() : [];

        $this->logMany('permission-attached', $addedNames);
        $this->logMany('permission-detached', $removedNames);

        return $res;
    }
}
