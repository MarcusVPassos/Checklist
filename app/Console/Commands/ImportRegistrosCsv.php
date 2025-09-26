<?php

namespace App\Console\Commands;

use App\Models\Imagem;
use App\Models\Marcas;
use App\Models\Registros;
use App\Models\User;
use App\Models\Itens;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use SplFileObject;

class ImportRegistrosCsv extends Command
{
    protected $signature = 'registros:import 
    {csv : Caminho do CSV}
    {--images-dir= : Pasta onde estão as imagens (mesmos nomes do CSV)}
    {--delimiter=, : Delimitador (padrão ,) }
    {--encoding=UTF-8 : Encoding (UTF-8|ISO-8859-1) }
    {--tipo-default=carro : Tipo padrão quando não for possível inferir}
    {--assinatura=reboque : tecnico|reboque (qual coluna usar para assinatura_path)}
    {--assinatura-required=0 : 1 exige assinatura; 0 não exige}
    {--user-fallback= : user_id padrão quando não encontrar}
    {--criar-marca=1 : 1 cria marca ausente; 0 exige existir}
    {--dry-run : Simula sem gravar}';

    protected $description = 'Importa Registros + Imagens do CSV para as tabelas registros/imagens com nosso padrão.';

    /* ===================== MAPAS / NORMALIZAÇÃO ===================== */

    private array $marcaAlias = [
        'Volkswagen' => ['vw', 'volks', 'volksvagen', 'volkswagen', 'wolkswagen', 'wokswagen', 'volksvagem'],
        'Chevrolet'  => ['gm', 'chevy', 'chevrolet'],
        'Fiat'       => ['fiat', 'fita', 'fiar'],
        'Ford'       => ['ford','frod','fond'],
        'Renault'    => ['renault', 'renalut', 'renauld', 'renout'],
        'Peugeot'    => ['peugeot', 'pegeot', 'pegeout', 'peugot', 'pgt', 'pgot'],
        'Citroën'    => ['citroen', 'citroën'],
        'Honda'      => ['honda'],
        'Hyundai'    => ['hyundai', 'hyndai', 'hiunday', 'hiundayi'],
        'Toyota'     => ['toyota', 'toyta', 'tayota'],
        'Nissan'     => ['nissan', 'nisan'],
        'Jeep'       => ['jeep'],
        'BMW'        => ['bmw'],
        'Chery'      => ['chery', 'cherry', 'cherie'],
        'JAC'        => ['jac', 'j.a.c'],
        'Kia'        => ['kia', 'kia motors', 'kía'],
        'Mitsubishi' => ['mitsubishi', 'mitshubishi', 'mitsubish'],
        'Yamaha'     => ['yamaha'],
    ];

    private array $modeloAlias = [
        'etyos' => 'Etios',
        'hb20'  => 'HB20',
        'onix'  => 'Onix',
        'mobi'  => 'Mobi',
        'gol'   => 'Gol',
        'voyage' => 'Voyage',
        'civic' => 'Civic',
        'uno drive' => 'Uno Drive',
    ];

    private array $itemAliases = [
        'ALARME'               => ['alarme'],
        'AR CONDICIONADO'      => ['ar condicionado', 'ar-condicionado', 'arcondicionado', 'a/c'],
        'BATERIA'              => ['bateria'],
        'CALOTAS'              => ['calota', 'calotas'],
        'CHAVE CODIFICADA'     => ['chave codificada', 'chaves codificadas', 'chave'],
        'CÂMBIO AUTOMÁTICO'    => ['cambio automatico', 'câmbio automático', 'automatico', 'camb. automatico'],
        'CÂMBIO MANUAL'        => ['cambio manual', 'câmbio manual', 'manual', 'camb. manual'],
        'DIREÇÃO HIDRÁULICA'   => ['direcao hidraulica', 'direção hidráulica', 'dir. hidraulica'],
        'ESTEPE'               => ['estepe', 'pneu reserva'],
        'FAROL DE MILHA'       => ['farol de milha', 'milha'],
        'MACACO'               => ['macaco'],
        'ORIGINAL'             => ['original'],
        'PROTETOR DE CARTES'   => ['protetor de cartes', 'protetor de carter', 'protetor de cárter'],
        'RODAS DELIGA'         => ['rodas deliga', 'rodas de liga', 'rodas liga leve', 'rodas liga'],
        'RÁDIO ORIGINAL'       => ['radio original', 'rádio original', 'radio'],
        'TAPETES'              => ['tapete', 'tapetes'],
        'TETO SOLAR'           => ['teto solar'],
        'TRIÂNGULO'            => ['triangulo', 'triângulo'],
        'VIDRO ELÉTRICO'       => ['vidro eletrico', 'vidros eletricos', 'vidros elétricos'],
    ];

    /* ===================== CSV HEADERS ===================== */

    private array $mapCampos = [
        'DATA_HORA'                 => 'created_at_raw',
        'DATA HORA'                 => 'created_at_raw',
        'DATA/HORA'                 => 'created_at_raw',
        'DATA'                      => 'created_at_raw',
        'ID_TECNICO'                => 'usuario_id_raw',
        'RESPONSÁVEL'               => 'usuario_nome_raw',
        'MARCA | MODELO'            => 'marca_modelo_raw',
        'PLACA'                     => 'placa',
        'Nº MOTOR'                  => 'num_motor_csv',
        'ITENS DO VEÍCULO'          => 'itens_csv',
        'OBSERVAÇÃO'                => 'observacao',
        'NOME CONDUTOR DO REBOQUE'  => 'reboque_condutor',
        'PLACA DO REBOQUE'          => 'reboque_placa',
        'ASSINATURA | TÉCNICO'      => 'assin_tecnico',
        'ASSINATURA | REBOQUE'      => 'assin_reboque',
    ];

    private array $mapFotos = [
        'FOTO_FRENTE'             => 'frente',
        'FOTO_LATERAL_DIREITA'    => 'lado_direito',
        'FOTO_TRASEIRA'           => 'traseira',
        'FOTO_LATERAL_ESQUERDA'   => 'lado_esquerdo',
        'FOTO_ESTEPE'             => 'estepe_do_veiculo',
        'FOTO_CAPÔ_ABERTO'        => 'capo_aberto',
        'FOTO_N_MOTOR'            => 'numero_do_motor',
        'FOTO_PAINEL_DIREITO'     => 'painel_lado_direito',
        'FOTO_PAINEL_ESQUERDO'    => 'painel_lado_esquerdo',
        'FOTO_BATERIA'            => 'bateria',
        'FOTO_CHAVE'              => 'chave',
    ];

    private array $enumPosicoes = [
        'frente',
        'lado_direito',
        'lado_esquerdo',
        'traseira',
        'capo_aberto',
        'numero_do_motor',
        'painel_lado_direito',
        'painel_lado_esquerdo',
        'bateria_carro',
        'chave_carro',
        'estepe_do_veiculo',
        'motor_lado_direito',
        'motor_lado_esquerdo',
        'painel_moto',
        'chave_moto',
        'bateria_moto'
    ];

    /* ===================== Helpers de data ===================== */
    private function parseDataHora(?string $raw): ?Carbon
    {
        if (!$raw) return null;
        $s = trim(str_replace(["\xEF\xBB\xBF", "\xC2\xA0", '"', "'"], '', $raw));
        $s = preg_replace('/\s+/', ' ', $s);
        $tz = config('app.timezone', 'America/Sao_Paulo');
        $formatos = ['d/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y H\hi', 'd/m/Y', 'Y-m-d\TH:i:sP', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'];
        foreach ($formatos as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $s, $tz);
                if ($dt !== false) return $dt;
            } catch (\Throwable $e) {
            }
        }
        try {
            return Carbon::parse($s, $tz);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /* ===================== Normalização ===================== */
    private function stripAccentsLower(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = strtr($s, ['á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o', 'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n', 'ý' => 'y', 'ÿ' => 'y', 'œ' => 'oe', 'æ' => 'ae']);
        $s = preg_replace('/[^a-z0-9]+/u', ' ', $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    private function canonicalMarca(?string $raw): ?string
    {
        if (!$raw) return null;
        $key = $this->stripAccentsLower($raw);
        foreach ($this->marcaAlias as $canon => $aliases) {
            foreach ($aliases as $a) {
                if ($key === $this->stripAccentsLower($a) || $key === $this->stripAccentsLower($canon)) return $canon;
            }
        }
        return mb_convert_case(trim($raw), MB_CASE_TITLE, 'UTF-8');
    }

    private function normalizeModelo(?string $raw): ?string
    {
        if (!$raw) return null;
        $k = $this->stripAccentsLower($raw);
        if (isset($this->modeloAlias[$k])) return $this->modeloAlias[$k];
        $t = mb_convert_case($k, MB_CASE_TITLE, 'UTF-8');
        $t = preg_replace('/\bHb20\b/u', 'HB20', $t);
        return $t;
    }

    /* ===================== Helpers de imagens ===================== */
    private function normalizeFilename(?string $nome): ?string
    {
        if (!$nome) return null;
        $s = str_replace('\\', '/', trim($nome));
        $s = trim($s, " \t\n\r\0\x0B\"'");
        $s = preg_replace('/\s+/', ' ', $s);
        return basename($s);
    }

    private function resolveImagePath(string $imagesDir, ?string $arquivo): ?string
    {
        if (!$arquivo) return null;
        $name = $this->normalizeFilename($arquivo);
        if (!$name) return null;
        $path = "{$imagesDir}/{$name}";
        if (is_file($path)) return $path;
        $lower = mb_strtolower($name, 'UTF-8');
        foreach (scandir($imagesDir) ?: [] as $f) {
            if ($f === '.' || $f === '..') continue;
            if (mb_strtolower($f, 'UTF-8') === $lower) return "{$imagesDir}/{$f}";
        }
        return null;
    }

    /* ===================== Helpers de PLACA ===================== */
    private int $placaSeq = 0;

    private function sanitizePlaca(?string $p): ?string
    {
        if (!$p) return null;
        $p = strtoupper(trim($p));
        $p = preg_replace('/\s+/', '', $p);
        if ($p === '' || $p === '?' || $p === 'NULL') return null;
        return $p;
    }

    private function gerarPlacaPlaceholder(): string
    {
        do {
            $this->placaSeq++;
            $cand = 'XXXXXX' . $this->placaSeq;
        } while (Registros::where('placa', $cand)->exists());
        return $cand;
    }

    /* ===================== Comando ===================== */
    public function handle(): int
    {
        $assinaturaRequired   = (bool) ((int) $this->option('assinatura-required'));
        $csvPath              = $this->argument('csv');
        $imagesDir            = rtrim((string) $this->option('images-dir'), '/');
        $delimiter            = (string) $this->option('delimiter');
        $encoding             = strtoupper((string) $this->option('encoding') ?: 'UTF-8');
        $tipoDefault          = strtolower((string) $this->option('tipo-default') ?: 'carro');
        $assinaturaPreferida  = strtolower((string) $this->option('assinatura') ?: 'reboque');
        $userFallback         = $this->option('user-fallback');
        $criarMarca           = (bool) ((int) $this->option('criar-marca'));
        $dryRun               = (bool) $this->option('dry-run');

        if (!is_file($csvPath)) {
            $this->error("CSV não encontrado: {$csvPath}");
            return self::FAILURE;
        }
        if ($imagesDir && !is_dir($imagesDir)) {
            $this->error("Pasta de imagens não encontrada: {$imagesDir}");
            return self::FAILURE;
        }

        $file = new SplFileObject($csvPath);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($delimiter);

        $headers = $file->fgetcsv();
        if (!$headers) {
            $this->error('CSV sem cabeçalho.');
            return self::FAILURE;
        }
        $headers = array_map(fn($h) => $this->norm((string)$h, $encoding), $headers);
        $posByHeader = [];
        foreach ($headers as $i => $h) $posByHeader[$h] = $i;

        $hasItensCol = Schema::hasColumn('registros', 'itens');
        $pivot = $this->detectItensPivot(); // null se não existir pivô

        $lin = 1;
        $criadas = 0;
        $atualizadas = 0;
        $fotosOk = 0;
        $fotosFalhas = 0;
        $assinOk = 0;
        $assinFalha = 0;

        DB::beginTransaction();
        try {
            while (!$file->eof()) {
                $row = $file->fgetcsv();
                if ($row === [null] || $row === false) continue;
                $lin++;

                $rowAssoc = [];
                foreach ($posByHeader as $h => $idx) $rowAssoc[$h] = $this->norm(isset($row[$idx]) ? (string)$row[$idx] : '', $encoding);

                [$payload, $fotoCols, $assinTecnico, $assinReboque] = $this->montarPayload($rowAssoc);

                // PLACA
                $placa = $this->sanitizePlaca($payload['placa'] ?? null);
                if (!$placa) $placa = $this->gerarPlacaPlaceholder();
                $payload['placa'] = $placa;

                // Tipo
                $payload['tipo'] = $this->inferirTipo($payload, $tipoDefault);

                // Marca|Modelo
                [$marcaNomeRaw, $modeloRaw] = $this->splitMarcaModelo($payload['marca_modelo_raw'] ?? null);
                unset($payload['marca_modelo_raw']);
                $marcaCanon = $this->canonicalMarca($marcaNomeRaw ?: '');
                $payload['marca_id'] = $marcaCanon ? $this->resolverMarca($marcaCanon, $criarMarca) : null;
                $modeloNorm = $this->normalizeModelo($modeloRaw ?: '');
                $payload['modelo'] = ($modeloNorm && $modeloNorm !== '?') ? $modeloNorm : 'DESCONHECIDO';

                // user_id
                $payload['user_id'] = $this->resolverUser(
                    $payload['usuario_id_raw'] ?? null,
                    $payload['usuario_nome_raw'] ?? null,
                    $userFallback
                );
                unset($payload['usuario_id_raw'], $payload['usuario_nome_raw']);

                // ITENS DO VEÍCULO
                $itemIds = $this->itensStringToIds($payload['itens_csv'] ?? null);
                $payload['itens'] = $this->itensToDisplayString($itemIds);
                unset($payload['itens_csv']);

                if (!$pivot && !$hasItensCol && !empty($itemIds)) {
                    $payload['observacao'] = trim(($payload['observacao'] ?? '') .
                        (($payload['observacao'] ?? '') !== '' ? "\n" : '') .
                        '[Itens] ' . implode(', ', $this->idsToItemNames($itemIds)));
                }

                // ===== Status via observação (ANTES de montar createData/updateData) =====
                if ($this->observacaoIndicaSaida($payload['observacao'] ?? null)) {
                    $payload['no_patio'] = false; // saiu
                } elseif (!array_key_exists('no_patio', $payload) || $payload['no_patio'] === null) {
                    $payload['no_patio'] = true; // default
                }

                // timestamps
                $now = Carbon::now(config('app.timezone', 'America/Sao_Paulo'));
                if (!empty($payload['created_at_raw'])) {
                    $ts = $this->parseDataHora($payload['created_at_raw']);
                    if (!$ts) $ts = $now;
                } else {
                    $ts = $now;
                }
                $payload['created_at'] = $ts;
                $payload['updated_at'] = $ts;
                unset($payload['created_at_raw']);

                // assinatura preferida
                $assinArquivo = $assinaturaPreferida === 'reboque'
                    ? ($assinReboque ?: null)
                    : ($assinTecnico ?: ($assinReboque ?: null));

                if ($assinaturaPreferida === 'reboque' && $assinaturaRequired) {
                    if (!$assinArquivo) {
                        $this->warn("Linha {$lin}: sem assinatura (reboque) — ignorada.");
                        continue;
                    }
                    if (!$this->resolveImagePath($imagesDir, $assinArquivo)) {
                        $this->warn("Linha {$lin}: assinatura não encontrada: {$assinArquivo} — ignorada.");
                        continue;
                    }
                }

                // Upsert por PLACA
                $registro = Registros::where('placa', $placa)->first();

                if (!$registro) {
                    if (!$dryRun) {
                        $keys = ['tipo', 'placa', 'marca_id', 'user_id', 'modelo', 'observacao', 'reboque_condutor', 'reboque_placa', 'no_patio'];
                        if ($hasItensCol) $keys[] = 'itens';
                        $createData = $this->only($payload, $keys);

                        $registro = new Registros($createData);
                        $registro->assinatura_path = '';
                        $registro->created_at = $payload['created_at'];
                        $registro->updated_at = $payload['updated_at'];
                        $registro->timestamps = false;
                        $registro->save();
                        $registro->timestamps = true;

                        // Relaciona itens se existir pivô
                        if ($pivot && !empty($itemIds)) {
                            $registro->itens()->syncWithoutDetaching($itemIds);
                        }
                    }
                    $criadas++;
                } else {
                    if (!$dryRun) {
                        $keys = ['tipo', 'marca_id', 'user_id', 'modelo', 'observacao', 'reboque_condutor', 'reboque_placa', 'no_patio'];
                        if ($hasItensCol) $keys[] = 'itens';
                        $updateData = $this->only($payload, $keys);

                        $registro->fill($updateData);
                        if (empty($registro->created_at) || (is_string($registro->created_at) && $registro->created_at === '0000-00-00 00:00:00')) {
                            $registro->created_at = $payload['created_at'];
                        }
                        $registro->updated_at = $payload['updated_at'];
                        $registro->timestamps = false;
                        $registro->save();
                        $registro->timestamps = true;

                        if ($pivot && !empty($itemIds)) {
                            $registro->itens()->syncWithoutDetaching($itemIds);
                        }
                    }
                    $atualizadas++;
                }

                // Copiar assinatura
                if (($registro ?? null) && $assinArquivo && $imagesDir) {
                    $ok = $this->copiarAssinatura($registro->id, $assinArquivo, $imagesDir, $dryRun);
                    $ok ? $assinOk++ : $assinFalha++;
                }

                // FOTOS
                if ($registro) {
                    foreach ($fotoCols as $csvCol => $posicaoBase) {
                        $nomeArquivo = $this->normalizeFilename($rowAssoc[$csvCol] ?? '');
                        if ($nomeArquivo === '' || $nomeArquivo === null) continue;

                        $posicao = $this->resolverPosicao($posicaoBase, $payload['tipo'] ?? $tipoDefault);
                        if (!in_array($posicao, $this->enumPosicoes, true)) {
                            $this->warn("Linha {$lin}: posição inválida '{$posicao}' (coluna {$csvCol}) — ignorada.");
                            continue;
                        }
                        $ok = $this->copiarFoto($registro->id, $posicao, $nomeArquivo, $imagesDir, $dryRun);
                        $ok ? $fotosOk++ : $fotosFalhas++;
                    }
                }
            }

            $dryRun ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Falha: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Concluído. Criadas: {$criadas}, Atualizadas: {$atualizadas}");
        $this->info("Assinaturas OK: {$assinOk}, Assinaturas não encontradas: {$assinFalha}");
        $this->info("Fotos OK: {$fotosOk}, Fotos não encontradas: {$fotosFalhas}");
        $this->line($dryRun ? '(simulação: nada foi gravado)' : '');
        return self::SUCCESS;
    }

    private function montarPayload(array $row): array
    {
        $p = [];
        $fotoCols = [];
        $assinTec = null;
        $assinReb = null;

        foreach ($this->mapCampos as $csvKey => $campo) {
            if (!array_key_exists($csvKey, $row)) continue;
            $val = $row[$csvKey] !== '' ? $row[$csvKey] : null;

            switch ($campo) {
                case 'created_at_raw':
                case 'usuario_id_raw':
                case 'usuario_nome_raw':
                case 'marca_modelo_raw':
                case 'observacao':
                case 'reboque_condutor':
                case 'reboque_placa':
                case 'placa':
                case 'itens_csv':
                    $p[$campo] = $val;
                    break;
                case 'assin_tecnico':
                    $assinTec = $val;
                    break;
                case 'assin_reboque':
                    $assinReb = $val;
                    break;
                default:
                    break;
            }
        }

        foreach ($this->mapFotos as $csvKey => $posicao) {
            if (array_key_exists($csvKey, $row)) $fotoCols[$csvKey] = $posicao;
        }

        return [$p, $fotoCols, $assinTec, $assinReb];
    }

    private function inferirTipo(array $payload, string $default): string
    {
        $modelo = strtolower($payload['marca_modelo_raw'] ?? $payload['modelo'] ?? '');
        if (preg_match('/\bmoto\b|\bcg\b|\bybr\b|\bfan\b|\bfazer\b/u', $modelo)) return 'moto';
        return in_array($default, ['carro', 'moto'], true) ? $default : 'carro';
    }

    private function splitMarcaModelo(?string $raw): array
    {
        if (!$raw) return [null, null];

        // se vier com "|"
        if (strpos($raw, '|') !== false) {
            [$m, $d] = array_map(fn($s) => trim($s), explode('|', $raw, 2));
            return [$m ?: null, $d ?: null];
        }

        // quebra por espaço
        $parts = preg_split('/\s+/', trim($raw));

        if (!$parts || !$parts[0]) return [null, null];

        if (count($parts) === 1) {
            // só um termo => é MODELO; não tentamos inferir marca
            return [null, $parts[0]];
        }

        // 2+ termos: primeiro como marca, resto como modelo
        $marca  = array_shift($parts);
        $modelo = implode(' ', $parts);

        return [$marca ?: null, ($modelo !== '') ? $modelo : null];
    }


    private function resolverMarca(?string $nomeCanon, bool $criar): ?int
    {
        if (!$nomeCanon) return null;

        $m = Marcas::whereRaw('LOWER(nome) = ?', [mb_strtolower($nomeCanon, 'UTF-8')])->first();
        if ($m) return $m->id;

        foreach ($this->marcaAlias as $canon => $aliases) {
            if ($canon === $nomeCanon) {
                foreach ($aliases as $a) {
                    $alt = Marcas::whereRaw('LOWER(nome) = ?', [mb_strtolower($a, 'UTF-8')])->first();
                    if ($alt) return $alt->id;
                }
            }
        }
        return $criar ? Marcas::create(['nome' => $nomeCanon])->id : null;
    }

    private function resolverUser($idTecnicoRaw, $responsavelNomeRaw, $fallback): ?int
    {
        if (is_numeric($idTecnicoRaw)) {
            $u = User::find((int) $idTecnicoRaw);
            if ($u) return $u->id;
        }
        if ($responsavelNomeRaw) {
            $u = User::where('name', $responsavelNomeRaw)->first();
            if ($u) return $u->id;
        }
        return $fallback ? (int) $fallback : null;
    }

    private function copiarAssinatura(int $registroId, string $arquivo, string $imagesDir, bool $dryRun): bool
    {
        $orig = $this->resolveImagePath($imagesDir, $arquivo);
        if (!$orig) {
            $this->warn("Assinatura não encontrada: {$arquivo}");
            return false;
        }
        $destName = $this->normalizeFilename($arquivo);
        $dest = "registros/{$registroId}/assinaturas/{$destName}";
        if (!$dryRun) {
            Storage::disk('public')->makeDirectory(dirname($dest));
            Storage::disk('public')->put($dest, file_get_contents($orig));
            Registros::whereKey($registroId)->update(['assinatura_path' => $dest]);
        }
        return true;
    }

    private function copiarFoto(int $registroId, string $posicao, string $arquivo, string $imagesDir, bool $dryRun): bool
    {
        $orig = $this->resolveImagePath($imagesDir, $arquivo);
        if (!$orig) {
            $this->warn("Foto não encontrada: {$arquivo} (posicao: {$posicao})");
            return false;
        }
        $destName = $this->normalizeFilename($arquivo);
        $dest = "registros/{$registroId}/fotos/{$destName}";
        if (!$dryRun) {
            Storage::disk('public')->makeDirectory(dirname($dest));
            Storage::disk('public')->put($dest, file_get_contents($orig));

            $img = Imagem::where('registro_id', $registroId)->where('posicao', $posicao)->first();
            $img ? $img->update(['path' => $dest])
                : Imagem::create(['registro_id' => $registroId, 'posicao' => $posicao, 'path' => $dest]);
        }
        return true;
    }

    private function resolverPosicao(string $base, string $tipo): string
    {
        if ($base === 'bateria') return $tipo === 'moto' ? 'bateria_moto' : 'bateria_carro';
        if ($base === 'chave')   return $tipo === 'moto' ? 'chave_moto'   : 'chave_carro';
        return $base;
    }

    private function norm(string $v, string $encoding): string
    {
        $v = trim($v);
        if ($encoding !== 'UTF-8') $v = mb_convert_encoding($v, 'UTF-8', $encoding);
        return $v;
    }

    private function only(array $arr, array $keys): array
    {
        return array_intersect_key($arr, array_flip($keys));
    }

    /* ===================== ITENS helpers ===================== */

    private function detectItensPivot(): ?array
    {
        $candidates = [
            ['registros_itens', 'registros_id', 'itens_id'],
            ['registro_itens', 'registro_id', 'item_id'],
            ['registros_itens', 'registro_id', 'item_id'],
            ['itens_registro', 'registro_id', 'item_id'],
            ['itens_registros', 'registro_id', 'item_id'],
            ['registro_itens', 'registro_id', 'itens_id'],
            ['registros_itens', 'registro_id', 'itens_id'],
            ['itens_registro', 'registros_id', 'item_id'],
            ['itens_registros', 'registros_id', 'item_id'],
        ];
        foreach ($candidates as [$table, $fkReg, $fkItem]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $fkReg) && Schema::hasColumn($table, $fkItem)) {
                return ['table' => $table, 'registro_fk' => $fkReg, 'item_fk' => $fkItem];
            }
        }
        return null;
    }

    // ===== Helpers Status (leitura da observação) =====
    private function observacaoIndicaSaida(?string $obs): bool
    {
        if (!$obs) return false;
        $t = $this->stripAccentsLower($obs);
        $gatilhos = [
            'saiu',
            'saiu do patio',
            'saiu do patio hoje',
            'fora do patio',
            'retirado do patio',
            'retirada do patio',
            'veiculo saiu',
            'carro saiu',
        ];
        foreach ($gatilhos as $g) if (str_contains($t, $g)) return true;
        return (bool) preg_match('/\bsaiu\b/u', $t);
    }

    private function itensStringToIds(?string $raw): array
    {
        $names = $this->splitItens($raw);
        if (empty($names)) return [];
        $ids = [];
        foreach ($names as $name) {
            $canonical = $this->canonicalItem($name);
            $item = Itens::firstOrCreate(['nome' => $canonical]);
            $ids[] = $item->id;
        }
        return array_values(array_unique($ids));
    }

    private function itensToDisplayString(array $itemIds): ?string
    {
        if (empty($itemIds)) return null;
        return implode(', ', $this->idsToItemNames($itemIds));
    }

    private function idsToItemNames(array $ids): array
    {
        if (empty($ids)) return [];
        return Itens::whereIn('id', $ids)->pluck('nome')->all();
    }

    private function splitItens(?string $raw): array
    {
        if ($raw === null) return [];
        $s = trim($raw);
        if ($s === '') return [];
        $s = str_replace(["\r", "\n", "\t", "/", "|", ";"], ',', $s);
        $parts = array_map('trim', explode(',', $s));
        $out = [];
        foreach ($parts as $p) {
            if ($p === '') continue;
            $key = $this->stripAccentsLower($p);
            if (!isset($out[$key])) $out[$key] = $p;
        }
        return array_values($out);
    }

    private function canonicalItem(string $raw): string
    {
        $needle = $this->stripAccentsLower($raw);
        foreach ($this->itemAliases as $canon => $aliases) {
            foreach ($aliases as $alias) {
                if ($needle === $this->stripAccentsLower($alias)) return $canon;
            }
            if ($needle === $this->stripAccentsLower($canon)) return $canon;
        }
        return mb_strtoupper(trim($raw), 'UTF-8');
    }
}
