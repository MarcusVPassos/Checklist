<?php

namespace Database\Seeders;

use App\Models\Marcas;
use App\Models\Registros;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class RegistroSeeder extends Seeder
{
    public function run(): void
    {
        $meses = 4;
        $registrosPorMes = 20;

        // pegue todos os IDs de marcas existentes
        $marcaIds = Marcas::query()->pluck('id')->all();

        $dataBase = Carbon::now()->subMonths($meses - 1)->startOfMonth();

        for ($i = 0; $i < $meses; $i++) {
            for ($j = 0; $j < $registrosPorMes; $j++) {
                Registros::factory()->create([
                    'marca_id'   => !empty($marcaIds) ? Arr::random($marcaIds) : 1, // fallback 1
                    'created_at' => $dataBase->copy()->addDays(rand(0, $dataBase->daysInMonth - 1)),
                    'updated_at' => now(),
                ]);
            }
            $dataBase->addMonth()->startOfMonth();
        }
    }
}
