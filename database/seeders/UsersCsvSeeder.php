<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ajuste os e-mails se preferir os corporativos reais.
        $usuarios = [
            [
                'name'  => 'Erculano',
                'email' => 'erculano@example.com',
                'password' => Hash::make('senha123'), // troque em produção
            ],
            [
                'name'  => 'Douglas',
                'email' => 'douglas@example.com',
                'password' => Hash::make('senha123'), // troque em produção
            ],
        ];

        foreach ($usuarios as $u) {
            User::updateOrCreate(
                ['email' => $u['email']], // chave única
                [
                    'name' => $u['name'],
                    'password' => $u['password'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
