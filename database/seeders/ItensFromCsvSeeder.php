<?php

namespace Database\Seeders;

use App\Models\Itens;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItensFromCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Itens únicos extraídos do CSV (19)
        $itens = [
            'ALARME',
            'AR CONDICIONADO',
            'BATERIA',
            'CALOTAS',
            'CHAVE CODIFICADA',
            'CÂMBIO AUTOMÁTICO',
            'CÂMBIO MANUAL',
            'DIREÇÃO HIDRÁULICA',
            'ESTEPE',
            'FAROL DE MILHA',
            'MACACO',
            'ORIGINAL',
            'PROTETOR DE CARTES', // veio assim no CSV
            'RODAS DELIGA',       // veio assim no CSV
            'RÁDIO ORIGINAL',
            'TAPETES',
            'TETO SOLAR',
            'TRIÂNGULO',
            'VIDRO ELÉTRICO',
        ];

        foreach ($itens as $nome) {
            Itens::firstOrCreate(['nome' => $nome]);
        }
    }
}
