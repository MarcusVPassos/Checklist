<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Clear cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2) Permissões 
        $permissoes = [
            // Usuários
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.assign-roles',
            'users.assign-permissions',

            //Registros 
            'registros.view',
            'registros.create',
            'registros.update',
            'registros.delete',
            'registros.restore',
            'registros.force-delete',

            // Auditoria
            'logs.view',
        ];

        foreach ($permissoes as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 3) Papéis
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $gestor = Role::firstOrCreate(['name' => 'gestor', 'guard_name' => 'web']);
        $colab = Role::firstOrCreate(['name' => 'colaborador', 'guard_name' => 'web']);
        $regu = Role::firstOrCreate(['name' => 'regulagem', 'guard_name' => 'web']);

        // 4) Atribuição de permissões aos papéis
        $admin->syncPermissions(Permission::all()); // admin total
        $gestor->syncPermissions([
            'users.view',
            'users.create',
            'users.update',
            'registros.view',
            'registros.create',
            'registros.update',
            'registros.restore',
            'registros.delete',
            'users.assign-roles',
            'users.assign-permissions',
        ]);
        $colab->syncPermissions([
            'registros.view',
            'registros.create',
        ]);
        $regu->syncPermissions([
            'registros.view',
        ]);
    }
}
