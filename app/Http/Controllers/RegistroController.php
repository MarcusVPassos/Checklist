<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroStoreRequest; // FormRequest com validação centralizada (melhor prática)
use App\Http\Requests\RegistroUpdateRequest;
use App\Models\Imagem;
use App\Models\Itens;
use App\Models\Marcas;
use App\Models\Registros;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB; // Para transações atômicas (begin/commit/rollback)
use Illuminate\Support\Facades\Log; // Para registrar sucessos/erros em logs
use Illuminate\Support\Facades\Storage; // Para salvar arquivos no disco configurado

class RegistroController extends Controller
{
    public function index()
    {
        /*
         * Objetivo: carregar a listagem de forma leve e rápida.
         * - select(): traz só as colunas que a grade precisa.
         * - with(): eager loading controlado para evitar N+1 (apenas campos necessários).
         * - latest('id'): ordenação do mais novo para o mais antigo.
         * - cursorPaginate(6): paginação por cursor → ideal para "Carregar mais" sem custo crescente de OFFSET.
         */
        $registros = Registros::query()
            ->select(['id', 'placa', 'tipo', 'no_patio', 'marca_id', 'modelo', 'assinatura_path'])
            ->with([
                'marca:id,nome', // join leve apenas com os campos usados
                'imagens:id,registro_id,posicao,path', // apenas o necessário para mostrar a capa
            ])
            ->latest('id')
            ->cursorPaginate(6); // melhor para "load more"

        // Retorna para a view Blade com a coleção paginada por cursor     
        return view('registros.index', compact('registros'));
    }

    /*
     * Endpoint de detalhes "sob demanda" para modal.
     * - Usamos Route Model Binding (Registros $registro).
     * - Carregamos apenas quando o usuário abre o modal → menos peso na página principal.
     * - Retornamos JSON estruturado, pronto para o Alpine/JS montar o modal.
     */
    public function show(Registros $registro)
    {
        try {
            // Rótulos amigáveis para as posições de fotos (usado no front)
            $rotulos = [
                'frente' => 'Frente',
                'lado_direito' => 'Lado direito',
                'lado_esquerdo' => 'Lado esquerdo',
                'traseira' => 'Traseira',
                'capo_aberto' => 'Capô aberto',
                'numero_do_motor' => 'Número do motor',
                'painel_lado_direito' => 'Painel (lado direito)',
                'painel_lado_esquerdo' => 'Painel (lado esquerdo)',
                'bateria_carro' => 'Bateria (carro)',
                'chave_carro' => 'Chave (carro)',
                'estepe_do_veiculo' => 'Estepe',
                'motor_lado_direito' => 'Motor (lado direito)',
                'motor_lado_esquerdo' => 'Motor (lado esquerdo)',
                'painel_moto' => 'Painel (moto)',
                'chave_moto' => 'Chave (moto)',
                'bateria_moto' => 'Bateria (moto)',
            ];

            // Carrega relações necessárias para o modal
            $registro->load([
                'marca:id,nome',
                'itens:id,nome', // relação N:N (pivot) – apenas os nomes usados na UI
                'imagens:id,registro_id,posicao,path', // imagens para galeria/slides do modal
            ]);

            // Monta resposta JSON com campos prontos para o front
            return response()->json([
                'id'               => $registro->id,
                'placa'            => $registro->placa,
                'tipo'             => $registro->tipo,
                'no_patio'         => (bool) $registro->no_patio, // coerção para boolean
                'marca'            => $registro->marca?->nome,
                'modelo'           => $registro->modelo,
                'observacao'       => $registro->observacao,
                'reboque_condutor' => $registro->reboque_condutor,
                'reboque_placa'    => $registro->reboque_placa,
                'assinatura'       => $registro->assinatura_path ? asset('storage/' . $registro->assinatura_path) : null,
                // datas em ISO 8601 (padrão robusto para JS)
                'created_at'       => optional($registro->created_at)->toIso8601String(),
                'updated_at'       => optional($registro->updated_at)->toIso8601String(),
                // itens do pivot já “flattened” para array de nomes
                'itens'            => $registro->itens->pluck('nome')->values(),
                // slides prontos para o carrossel, com label humano
                'slides'           => $registro->imagens->map(fn($img) => [
                    'url'     => asset('storage/' . $img->path),
                    'posicao' => $img->posicao,
                    'label'   => $rotulos[$img->posicao] ?? $img->posicao,
                ])->values(),
            ]);
        } catch (\Throwable $e) {
            // Log de erro com contexto (id) para depuração
            Log::error('registros.show', ['id' => $registro->id, 'err' => $e->getMessage()]);
            return response()->json(['message' => 'Falha ao carregar o registro.'], 500);
        }
    }

    public function create()
    {
        /**
         * Carrega dados necessários para o formulário:
         * - Marcas e Itens vão popular selects/checkboxes.
         * - OrderBy para UX (lista em ordem alfabética).
         */
        return view('registros.create', [
            'marcas' => Marcas::orderBy('nome')->get(),
            'itens' => Itens::orderBy('nome')->get(),
        ]);
    }

    public function store(RegistroStoreRequest $request)
    {
        /*
         * Envolve todo o processo em uma transação para garantir consistência:
         * - Se qualquer etapa falhar (criar registro, salvar assinatura, imagens, pivot), tudo é revertido.
         * - Mantém o banco sincronizado com os arquivos no storage (melhor integridade).
         */
        return DB::transaction(function () use ($request) {
            // Dados já validados pela FormRequest (sanitizados e com regras aplicadas)
            $data = $request->validated();

            /*
             * 1) Cria o registro (sem imagens e com assinatura_path vazio por enquanto).
             * - Precisamos do ID do registro para montar os diretórios/nomes dos arquivos.
             * - assinatura_path é atualizado após gravarmos o arquivo Base64.
             */
            $registro = Registros::create([
                'tipo'              => $data['tipo'],
                'placa'             => $data['placa'],
                'marca_id'          => $data['marca_id'],
                'modelo'            => $data['modelo'],
                'no_patio'          => $data['no_patio'],
                'observacao'        => $data['observacao'] ?? null,
                'reboque_condutor'  => $data['reboque_condutor'],
                'reboque_placa'     => $data['reboque_placa'],
                'assinatura_path'  => '',
            ]);

            /**
             * 2) Assinatura (Base64 → arquivo):
             * - Recebe no campo "assinatura_b64" algo como: "data:image/png;base64,AAAA..."
             * - Separar metadados do payload, detectar extensão, decodificar e salvar no storage/public.
             */
            [$meta, $payload] = explode(',', $data['assinatura_b64'], 2); // [ "data:image/png;base64", "AAA..." ]
            preg_match('/^data:image\\/(png|jpe?g|webp);base64/i', $meta, $m);
            $ext = strtolower($m[1] ?? 'png'); // fallback seguro para png
            if ($ext === 'jpeg') $ext = 'jpg'; // normaliza para 'jpg'

            // 3) renomeia p/ nome final e atualiza o caminho NO BANCO
            // $stamp   = now()->format('Ymd_His');
            // $assExt  = $request->file('assinatura')->extension(); // ou getClientOriginalExtension()

            // Decodifica o Base64 em binário
            $bin    = base64_decode($payload);
            // carimbo de data/hora para nomes únicos e rastreáveis
            $stamp  = now()->format('Ymd_His');
            // diretório por registro → organização e possibilidade de limpeza pontual || "carimbo" p/ nome único
            $assDir  = "assinaturas/{$registro->id}";
            // nome de arquivo padronizado (facilita localizar no storage)
            $assName = "checklist_ass_{$stamp}.{$ext}";        // nome final
            $target  = "{$assDir}/{$assName}";

            // garante que a pasta exista e grava o arquivo no disco "public"
            Storage::disk('public')->makeDirectory($assDir);
            Storage::disk('public')->put($target, $bin);

            // Atualiza o caminho verdadeiro da assinatura no banco
            $registro->update(['assinatura_path' => $target]);

            /**
             * 3) Itens (relação N:N):
             * - Sincroniza a tabela pivô com os IDs recebidos.
             * - Se nada vier, mantém vazio (sem erro).
             */
            $registro->itens()->sync($data['itens'] ?? []);

            /**
             * 4) Upload das imagens por posição:
             * - Cada posição é 1:1 dentro do registro (updateOrCreate).
             * - Para CARRO, você padronizou nome: "checklist_car_{datahora}.{ext}".
             * - Para MOTO, manteve store() padrão (hash) — como alinhado anteriormente.
             */
            $todasPosicoes = [
                // para os dois 
                'frente',
                'lado_direito',
                'lado_esquerdo',
                'traseira',
                // carro (obrigatórias)
                'capo_aberto',
                'numero_do_motor',
                'painel_lado_direito',
                'painel_lado_esquerdo',
                // carro (opcionais)
                'bateria_carro',
                'chave_carro',
                'estepe_do_veiculo',
                // moto (obrigatórias)
                'motor_lado_direito',
                'motor_lado_esquerdo',
                'painel_moto',
                // moto (opcionais)
                'chave_moto',
                'bateria_moto',
            ];

            foreach ($todasPosicoes as $pos) {
                // Se o input dessa posição não veio como arquivo, pula
                if (!$request->hasFile($pos)) continue;

                $file  = $request->file($pos);
                $ext   = $file->extension(); // extensão inferida (segura por validação)
                $dir   = "registros/{$registro->id}/{$pos}"; // organiza por registro/posição

                if ($data['tipo'] === 'carro') {
                    // Para carro, padroniza nome amigável + timestamp (facilita manutenção)
                    $fname = "checklist_car_{$stamp}.{$ext}";
                    $path  = $file->storeAs($dir, $fname, 'public');
                } else {
                    // Para moto mantém seu fluxo atual (hash automático)
                    $path  = $file->store($dir, 'public');
                }

                // Garante 1:1 por posição: se já existir, atualiza; senão cria
                Imagem::updateOrCreate(
                    ['registro_id' => $registro->id, 'posicao' => $pos],
                    [
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime'          => $file->getClientMimeType(),
                        'size'          => $file->getSize(),
                    ]
                );
            }
            // Log de sucesso com o ID recém-criado (auditoria)
            Log::info('registros.store:success', ['registro_id' => $registro->id]);
            /**
             * Retorno:
             * - Para app web: redireciona com flash message.
             * - (Se fosse API) devolveríamos JSON 201 com payload do registro.
             */
            return redirect()
                ->route('registros.index')
                ->with('success', 'Registro criado com sucesso!');
        });
    }

    public function edit(Registros $registro)
    {
        // Carrega relações necessárias para o form de edição
        $registro->load(['marca', 'imagens', 'itens']);

        $payload = [
            'registro' => $registro,
            'marcas'   => Marcas::orderBy('nome')->get(),
            'itens'    => Itens::orderBy('nome')->get(),
        ];

        // Se veio via fetch() com o header X-Requested-With, devolve só o HTML do form
        if (request()->ajax()) {
            return view('registros.partials.edit-form', $payload);
        }

        // Fallback (acessou /registros/{id}/edit direto no navegador)
        return view('registros.edit', $payload);
    }

    public function update(RegistroUpdateRequest $request, Registros $registro)
    {
        // Os logs são ótimos para depuração, mantenha-os!
        Log::info('UPDATE raw', $request->all());
        Log::info('UPDATE validated', $request->validated());

        $data = $request->validated();

        return DB::transaction(function () use ($request, $registro, $data) {

            // --- CORREÇÃO 1: ATUALIZAR DADOS PRINCIPAIS ---
            // Lista de colunas que pertencem diretamente à tabela 'registros'.
            $colunasRegistro = [
                'tipo',
                'placa',
                'marca_id',
                'modelo',
                'no_patio',
                'observacao',
                'reboque_condutor',
                'reboque_placa'
            ];

            $registroData = Arr::only($data, $colunasRegistro);

            // 2) Assinatura (se uma nova foi enviada)
            if (!empty($data['assinatura_b64'])) {
                // Decodifica a nova assinatura
                [$meta, $b64] = explode(',', $data['assinatura_b64'], 2);
                $ext = str_contains($meta, 'image/png') ? 'png' : (str_contains($meta, 'image/webp') ? 'webp' : 'jpg');
                $bin = base64_decode($b64);

                // Define um caminho único
                $dir = "assinaturas/{$registro->id}";
                $stamp = now()->format('Ymd_His');
                $path = "{$dir}/checklist_ass_{$stamp}.{$ext}";

                // Apaga a assinatura antiga do disco, se existir
                if ($registro->assinatura_path) {
                    Storage::disk('public')->delete($registro->assinatura_path);
                }
                // Salva a nova
                Storage::disk('public')->put($path, $bin);

                // Adiciona o novo caminho aos dados a serem atualizados
                $registroData['assinatura_path'] = $path;
            }

            // Atualiza todos os campos do registro de uma só vez.
            // O método update() preenche e salva.
            $registro->update($registroData);

            // 3) Sincroniza os itens (relação N:N)
            // Sua lógica aqui já estava correta.
            $registro->itens()->sync($data['itens'] ?? []);

            // 4) Remoção de imagens existentes
            // Sua lógica aqui já estava correta.
            if (!empty($data['remove_imagens'])) {
                $imagens = Imagem::whereIn('id', $data['remove_imagens'])
                    ->where('registro_id', $registro->id)->get();

                foreach ($imagens as $img) {
                    Storage::disk('public')->delete($img->path);
                    $img->delete();
                }
            }

            // --- CORREÇÃO 2: LÓGICA PARA ATUALIZAR/ADICIONAR IMAGENS POSICIONAIS ---
            // Reutilizamos a mesma lista de posições do método store.
            $todasPosicoes = [
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
                'bateria_moto',
            ];

            $stamp = now()->format('Ymd_His');

            foreach ($todasPosicoes as $pos) {
                // Se o input dessa posição não veio como um novo arquivo, pula para a próxima
                if (!$request->hasFile($pos)) {
                    continue;
                }

                $file = $request->file($pos);
                $ext  = $file->extension();
                $dir  = "registros/{$registro->id}/{$pos}";

                // Padroniza o nome do arquivo, como no store
                $fname = "checklist_{$data['tipo']}_{$stamp}.{$ext}";
                $path  = $file->storeAs($dir, $fname, 'public');

                // Procura por uma imagem existente nesta posição para este registro
                $imagemExistente = Imagem::where('registro_id', $registro->id)
                    ->where('posicao', $pos)
                    ->first();

                // Se encontrou, apaga o arquivo antigo do disco
                if ($imagemExistente) {
                    Storage::disk('public')->delete($imagemExistente->path);
                }

                // Usa updateOrCreate para garantir que só exista uma imagem por posição.
                // Ele vai ATUALIZAR a existente ou CRIAR uma nova.
                Imagem::updateOrCreate(
                    ['registro_id' => $registro->id, 'posicao' => $pos],
                    [
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime'          => $file->getClientMimeType(),
                        'size'          => $file->getSize(),
                    ]
                );
            }

            // Opcional: Lógica para imagens "extras" (se houver um campo 'imagens[]')
            if ($request->hasFile('imagens')) {
                foreach ($request->file('imagens') as $file) {
                    $path = $file->store('registros', 'public');

                    Imagem::create([
                        'registro_id' => $registro->id,
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            // Redireciona com sucesso
            return redirect()->route('registros.index')->with('success', 'Registro atualizado com sucesso!');
        });
    }

    // Soft delete função para puxar na rota e fazer o delete bem fofinho que só muda status em vez de deletar total
    public function destroy(Registros $registro)
    {
        $registro->delete();
        return back()->with('sucess', 'Registro enviado para a lixeira.');
    }

    // retorna a lista com todos os registros que estão com status deletado, com paginate de 6 igual a lista normal
    public function trashed(){
        $registros = Registros::onlyTrashed()
            ->select(['id','placa','tipo','no_patio','marca_id','modelo','assinatura_path','deleted_at'])
            ->with(['marca:id,nome','imagens:id,registro_id,posicao,path'])
            ->latest('id')
            ->cursorPaginate(6);

        return view('registros.trashed', compact('registros'));
    }

    // Restaura o item com status deletado para o status normal, voltando a lista de registros e podendo ser filtrado etc
    public function restore($id){
        $registro = Registros::withTrashed()->findOrFail($id); // inclui deletador
        $registro->restore();
        return redirect()->route('registros.trashed')->with('sucess', 'Registro restaurado com sucesso');
    }

    // Apagou assim é vala papai, nunca mais será visto. F
    public function forceDelete($id){
        $registro = Registros::withTrashed()->findOrFail($id);
        $registro->forceDelete(); // Hard Delete
        return redirect()->route('registros.trashed')->with('sucess', 'Registro deletado permanentemente');
    }
}
