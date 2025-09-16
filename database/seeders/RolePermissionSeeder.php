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

            // Exemplo: Registros (só pra ilustrar botões/visões)
            'registros.view',
            'registros.create',
            'registros.update',
            'registros.delete',
        ];

        foreach ($permissoes as $p){
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
            'users.view', 'users.create', 'users.update',
            'users.assign-roles', // gestor pode conceder papéis (ajuste conforme sua política)
            'registros.view','registros.create','registros.update',
        ]);
        $colab->syncPermissions([
            'registros.view','registros.create',
        ]);
        $regu->syncPermissions([
            'registros.view',
        ]);
    }
}
