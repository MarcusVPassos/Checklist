<?php

namespace App\Traits;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;

trait LogsRolePermissionChanges
{
    use HasRoles, HasPermissions {
        // roles
        HasRoles::assignRole as protected spatieAssignRole;
        HasRoles::removeRole as protected spatieRemoveRole;
        HasRoles::syncRoles  as protected spatieSyncRoles;

        // permissions
        HasPermissions::givePermissionTo    insteadof HasRoles;
        HasPermissions::revokePermissionTo  insteadof HasRoles;
        HasPermissions::syncPermissions     insteadof HasRoles;

        HasPermissions::givePermissionTo    as protected spatieGivePermissionTo;
        HasPermissions::revokePermissionTo  as protected spatieRevokePermissionTo;
        HasPermissions::syncPermissions     as protected spatieSyncPermissions;
    }

    protected bool $suppressRoleLog = false;
    protected bool $suppressPermLog = false;

    protected function resolvePermissionNames(array $items): array
    {
        $guard = method_exists($this, 'getDefaultGuardName')
            ? $this->getDefaultGuardName()
            : config('auth.defaults.guard');

        return collect($items)->flatten()->map(function ($it) use ($guard) {
            if ($it instanceof Permission) return $it->name;
            if (is_int($it) || (is_string($it) && ctype_digit($it))) {
                return optional(Permission::findById((int)$it, $guard))->name;
            }
            return is_string($it) && $it !== '' ? $it : null;
        })->filter()->values()->all();
    }

    protected function resolveRoleNames(array $items): array
    {
        $guard = method_exists($this, 'getDefaultGuardName')
            ? $this->getDefaultGuardName()
            : config('auth.defaults.guard');

        return collect($items)->flatten()->map(function ($it) use ($guard) {
            if ($it instanceof Role) return $it->name;
            if (is_int($it) || (is_string($it) && ctype_digit($it))) {
                return optional(Role::findById((int)$it, $guard))->name;
            }
            return is_string($it) && $it !== '' ? $it : null;
        })->filter()->values()->all();
    }

    protected function logMany(string $action, array $names, string $key): void
    {
        foreach ($names as $name) {
            $this->logActivity($action, [
                'target_id'   => $this->id,
                'target_name' => $this->name,
                $key          => $name,   // 'permission' ou 'role'
            ]);
        }
    }

    /* ===== PERMISSIONS ===== */

    public function givePermissionTo(...$permissions)
    {
        $res = $this->spatieGivePermissionTo(...$permissions);
        if (!$this->suppressPermLog) {
            foreach ($this->resolvePermissionNames($permissions) as $name) {
                $this->logActivity('permission-attached', [
                    'target_id'   => $this->id,
                    'target_name' => $this->name,
                    'permission'  => $name,
                ]);
            }
        }
        return $res;
    }

    public function revokePermissionTo(...$permissions)
    {
        $res = $this->spatieRevokePermissionTo(...$permissions);
        if (!$this->suppressPermLog) {
            foreach ($this->resolvePermissionNames($permissions) as $name) {
                $this->logActivity('permission-detached', [
                    'target_id'   => $this->id,
                    'target_name' => $this->name,
                    'permission'  => $name,
                ]);
            }
        }
        return $res;
    }

    public function syncPermissions(...$permissions)
    {
        $before = $this->permissions()->pluck('id')->all();

        $this->suppressPermLog = true;
        $res = $this->spatieSyncPermissions(...$permissions);
        $this->suppressPermLog = false;

        $after = $this->permissions()->pluck('id')->all();

        $added   = array_values(array_diff($after, $before));
        $removed = array_values(array_diff($before, $after));

        $this->logMany(
            'permission-attached',
            Permission::whereIn('id', $added)->pluck('name')->all(),
            'permission'
        );

        $this->logMany(
            'permission-detached',
            Permission::whereIn('id', $removed)->pluck('name')->all(),
            'permission'
        );

        return $res;
    }

    /* ===== ROLES ===== */

    public function assignRole(...$roles)
    {
        $res = $this->spatieAssignRole(...$roles);
        if (!$this->suppressRoleLog) {
            foreach ($this->resolveRoleNames($roles) as $name) {
                $this->logActivity('role-attached', [
                    'target_id'   => $this->id,
                    'target_name' => $this->name,
                    'role'        => $name,
                ]);
            }
        }
        return $res;
    }

    public function removeRole(...$roles)
    {
        $res = $this->spatieRemoveRole(...$roles);
        if (!$this->suppressRoleLog) {
            foreach ($this->resolveRoleNames($roles) as $name) {
                $this->logActivity('role-detached', [
                    'target_id'   => $this->id,
                    'target_name' => $this->name,
                    'role'        => $name,
                ]);
            }
        }
        return $res;
    }

    public function syncRoles(...$roles)
    {
        $before = $this->roles()->pluck('id')->all();

        $this->suppressRoleLog = true;
        $res = $this->spatieSyncRoles(...$roles);
        $this->suppressRoleLog = false;

        $after = $this->roles()->pluck('id')->all();
        $added   = array_values(array_diff($after, $before));
        $removed = array_values(array_diff($before, $after));

        $this->logMany('role-attached',  Role::whereIn('id', $added)->pluck('name')->all(), 'role');
        $this->logMany('role-detached',  Role::whereIn('id', $removed)->pluck('name')->all(), 'role');

        return $res;
    }
}
