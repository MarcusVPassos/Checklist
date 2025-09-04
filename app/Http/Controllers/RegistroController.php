<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroStoreRequest;
use App\Models\Imagem;
use App\Models\Itens;
use App\Models\Marcas;
use App\Models\Registros;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function store(RegistroStoreRequest $request) // Puxando a regra de validação escrita em RegistroStoreRequest.
    {
        return DB::transaction(function () use ($request) { // transaction -> se der erro durante a execução de algum envio encerra o processo e não envia nenhum dos dados.
            $data = $request->validated();

            // dd($request->hasFile('assinatura'), $request->file('assinatura'), $request->allFiles());  --> Debug para ver se estava chegando imagem
            $tmpPath = $request->file('assinatura')->store('assinaturas', 'public');

            // Cria o registro (sem itens/fotos/assinaturas por enquanto)
            $registro = Registros::create([
                'tipo' => $data['tipo'],
                'placa' => $data['placa'],
                'marca_id' => $data['marca_id'],
                'modelo' => $data['modelo'],
                'no_patio' => $data['no_patio'],
                'observacao' => $data['observacao'],
                'reboque_condutor' => $data['reboque_condutor'],
                'reboque_placa' => $data['reboque_placa'],
                'assinatura_path'  => $tmpPath,
            ]);

            // Assinatura (Obrigatória)
            $assinaturaPath = $request->file('assinatura')->store("assinaturas/{$registro->id}", 'public');
            $registro -> update(['assinatura_path' => $assinaturaPath]);

            //Itens N:N
            if (!empty($data['itens'])){
                $registro->itens()->sync($data['itens'] ?? []);  // se não vier, sincroniza vazio
            }

            foreach ($request->file('fotos', []) as $foto){
                $path = $foto->store("registros/{$registro->id}", 'public');
                Imagem::create([
                    'registro_id' => $registro->id,
                    'path' => $path,
                    // 'posicao' => 'frente' // se quiser tratar posições aqui
                ]);
            }

            return response()-> json(  // response json, para retorna o valor 201 de criado
                $registro->load(['marca', 'itens', 'imagens']), // eager loading para carregar os relacionamentos 
                201
            );
        });
    }
}
