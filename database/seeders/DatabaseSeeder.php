<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primeiro chama o seeder de roles e permissões
        $this->call([
            RolePermissionSeeder::class,
            UsersCsvSeeder::class,
            MarcasFromCsvSeeder::class,
            ItensFromCsvSeeder::class,
        ]);

        // Cria ou atualiza o usuário admin padrão
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'], //condicao
            [
                'name' => 'Admin',
                'password' => bcrypt('admin123'),
            ]
        );

        // Garante que ele sempre tenha o role 'admin'
        $admin->assignRole('admin');

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
