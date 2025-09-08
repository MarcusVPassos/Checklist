<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroStoreRequest;
use App\Models\Imagem;
use App\Models\Itens;
use App\Models\Marcas;
use App\Models\Registros;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class RegistroController extends Controller
{
    public function index(){
        // lista + eager loading para evitar N+1
        $registros = Registros::with(['marca', 'imagens'])->latest()->paginate(10);

        return view('registros.index', compact('registros'));
    }

    public function create(){
        return view('registros.create', [
            'marcas' => Marcas::orderBy('nome')->get(),
            'itens' => Itens::orderBy('nome')->get(),
        ]);
    }

    // public function store(RegistroStoreRequest $request) // Puxando a regra de validação escrita em RegistroStoreRequest.
    // {
    //     return DB::transaction(function () use ($request) { // transaction -> se der erro durante a execução de algum envio encerra o processo e não envia nenhum dos dados.
    //         $data = $request->validated();

    //         // dd($request->hasFile('assinatura'), $request->file('assinatura'), $request->allFiles());  --> Debug para ver se estava chegando imagem
    //         $tmpPath = $request->file('assinatura')->store('assinaturas', 'public'); // Faz primeiro o envio da foto depois o registro com as regras. Tenho que ver algo para ajustar isso

    //         // Cria o registro (sem itens/fotos/assinaturas por enquanto)
    //         $registro = Registros::create([
    //             'tipo' => $data['tipo'],
    //             'placa' => $data['placa'],
    //             'marca_id' => $data['marca_id'],
    //             'modelo' => $data['modelo'],
    //             'no_patio' => $data['no_patio'],
    //             'observacao' => $data['observacao'],
    //             'reboque_condutor' => $data['reboque_condutor'],
    //             'reboque_placa' => $data['reboque_placa'],
    //             'assinatura_path'  => $tmpPath,
    //         ]);

    //         // Assinatura (Obrigatória)
    //         // $assinaturaPath = $request->file('assinatura')->store("assinaturas/{$registro->id}", 'public');
    //         $finalPath = "assinaturas/{$registro->id}/" . basename($tmpPath);
    //         Storage::disk('public')->move($tmpPath, $finalPath);
    
    //         $registro -> update(['assinatura_path' => $finalPath]);

    //         //Itens N:N
    //         if (!empty($data['itens'])){
    //             $registro->itens()->sync($data['itens'] ?? []);  // se não vier, sincroniza vazio
    //         }

    //         foreach ($request->file('fotos', []) as $foto){
    //             $path = $foto->store("registros/{$registro->id}", 'public');
    //             Imagem::create([
    //                 'registro_id' => $registro->id,
    //                 'path' => $path,
    //                 // 'posicao' => 'frente' // se quiser tratar posições aqui
    //             ]);
    //         }

    //         return response()-> json(  // response json, para retorna o valor 201 de criado
    //             $registro->load(['marca', 'itens', 'imagens']), // eager loading para carregar os relacionamentos 
    //             201
    //         );
    //     });
    // }

public function store(RegistroStoreRequest $request)
    {
        return DB::transaction(function () use ($request) {

            $data = $request->validated();

            // $tmpPath = $request->file('assinatura')->store('assinaturas', 'public');

            // 1) Cria o registro (sem imagens ainda)
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

            // 2) Assinatura (Base64 -> arquivo no path final)
            // Espera "data:image/png;base64,AAAA..."
            [$meta, $payload] = explode(',', $data['assinatura_b64'], 2);
            preg_match('/^data:image\\/(png|jpe?g|webp);base64/i', $meta, $m);
            $ext = strtolower($m[1] ?? 'png');
            if ($ext === 'jpeg') $ext = 'jpg';

            // 3) renomeia p/ nome final e atualiza o caminho NO BANCO
            // $stamp   = now()->format('Ymd_His');
            // $assExt  = $request->file('assinatura')->extension(); // ou getClientOriginalExtension()

            $bin    = base64_decode($payload);
            $stamp  = now()->format('Ymd_His');               // "carimbo" p/ nome único
            $assDir  = "assinaturas/{$registro->id}";
            // $assName = "checklist_ass_{$stamp}.{$assExt}";
            $assName= "checklist_ass_{$stamp}.{$ext}";        // nome final
            $target  = "{$assDir}/{$assName}";

            Storage::disk('public')->makeDirectory($assDir);
            Storage::disk('public')->put($target, $bin);

            // grava o caminho verdadeiro
            $registro->update(['assinatura_path' => $target]);

            // 3) Itens (N:N)
            $registro->itens()->sync($data['itens'] ?? []);

            // 4) Imagens por posição (1:1 por posição dentro do registro)
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
                if (!$request->hasFile($pos)) continue;

                $file  = $request->file($pos);
                $ext   = $file->extension();
                $dir   = "registros/{$registro->id}/{$pos}";

                if ($data['tipo'] === 'carro') {
                    // Nome pedido: checklist_car_datahora
                    $fname = "checklist_car_{$stamp}.{$ext}";
                    $path  = $file->storeAs($dir, $fname, 'public');
                } else {
                    // Para moto mantém seu fluxo atual (hash automático)
                    $path  = $file->store($dir, 'public');
                }

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
            Log::info('registros.store:success', ['registro_id' => $registro->id]);
            // Se for API: retorna JSON; se for web: redireciona com flash
            return redirect()
                ->route('registros.index')
                ->with('success', 'Registro criado com sucesso!');
        });
    } 
}
