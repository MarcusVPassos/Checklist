<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pode conceder UMA permissão específica?
        Gate::define('assign-permission', function ($actor, string $permissionName) {
            // precisa ter o direito meta
            if (! $actor->can('users.assign-permissions')) return false;

            // só é delegável se estiver na whitelist
            return in_array($permissionName, config('permission.delegaveis', []), true);
        });

        // Pode conceder UM papel específico?
        Gate::define('assign-role', function ($actor, string $roleName, $targetUser = null) {
            if (! $actor->can('users.assign-roles')) return false;

            // nunca pode dar papéis bloqueados
            if (in_array($roleName, config('permission.roles_protegidos', []), true)) return false;

            // impedir auto-escalada (dar role para si mesmo)
            if ($targetUser && $actor->is($targetUser)) return false;

            return true;
        });

        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }
}
