<?php

namespace Database\Seeders;

use App\Models\Marcas;
use App\Models\Registros;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarcasFromCsvSeeder extends Seeder
{
    /**
     * Mapa de marca canônica -> aliases/erros mais comuns
     * (tudo em minúsculas e sem acento para bater fácil).
     */
    private array $alias = [
        'Volkswagen' => ['vw','volks','volksvagen','volkswagen','wolkswagen','wokswagen','volksvagem'],
        'Chevrolet'  => ['gm','chevy','chevrolet'],
        'Fiat'       => ['fiat','fita','fiar'],
        'Ford'       => ['ford','frod'],
        'Renault'    => ['renault','renalut','renauld','renout'],
        'Peugeot'    => ['peugeot','pegeot','pegeout','peugot','pgt','pgot'],
        'Citroën'    => ['citroen','citroën','citroen'],
        'Honda'      => ['honda'],
        'Hyundai'    => ['hyundai','hyndai','hiunday','hiundayi'],
        'Toyota'     => ['toyota','toyta','tayota'],
        'Nissan'     => ['nissan','nisan'],
        'Jeep'       => ['jeep'],
        'BMW'        => ['bmw'],
        'Chery'      => ['chery','cherry','cherie'],
        'JAC'        => ['jac','j.a.c'],
        'Kia'        => ['kia','kia motors','kía'],
        'Mitsubishi' => ['mitsubishi','mitshubishi','mitsubish'],
        'Yamaha'     => ['yamaha'],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            // 1) Garante que TODAS as canônicas existam
            foreach (array_keys($this->alias) as $canon) {
                Marcas::firstOrCreate(['nome' => $canon]);
            }

            // 2) Para cada canônica, migra os registros das variações p/ a canônica e apaga as duplicadas
            foreach ($this->alias as $canon => $variants) {
                $canonId = Marcas::where('nome', $canon)->value('id');
                if (!$canonId) continue;

                foreach ($variants as $v) {
                    // procura marca exatamente igual ao alias (case-insensitive)
                    $dup = Marcas::whereRaw('LOWER(nome) = ?', [mb_strtolower($v, 'UTF-8')])->first();
                    if ($dup && $dup->id !== $canonId) {
                        // Move os registros para a canônica
                        Registros::where('marca_id', $dup->id)->update(['marca_id' => $canonId]);
                        // Remove a duplicada
                        $dup->delete();
                    }
                }

                // 2.b) Se existir uma linha com mesmo nome "quase igual" (apenas diferente de caixa/acentuação), normalize
                $possiveis = Marcas::all();
                foreach ($possiveis as $m) {
                    if ($m->id === $canonId) continue;
                    if ($this->norm($m->nome) === $this->norm($canon)) {
                        Registros::where('marca_id', $m->id)->update(['marca_id' => $canonId]);
                        $m->delete();
                    }
                }
            }
        });
    }

    /** remove acentos, pontuação e baixa caixa */
    private function norm(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = strtr($s, ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n','ý'=>'y','ÿ'=>'y','œ'=>'oe','æ'=>'ae']);
        $s = preg_replace('/[^a-z0-9]+/u', ' ', $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }
}
