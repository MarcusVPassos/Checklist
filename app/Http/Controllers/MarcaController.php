<?php

namespace App\Http\Controllers;

use App\Models\Marcas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarcaController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $marcas = Marcas::query()
            ->when($q, fn($qr) => $qr->where('nome', 'like', "%{$q}%"))
            ->latest('id')
            ->paginate(15)
            ->withQueryString(); // mantém os filtros na paginação

        return view('marcas.index', compact('marcas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:30', Rule::unique('marcas', 'nome')],
        ]);

        Marcas::create($request->only('nome'));

        return redirect()->route('marcas.index')->with('success', 'Marca criada com sucesso.');
    }

    public function update(Request $request, $marcas_id)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:30', Rule::unique('marcas', 'nome')->ignore($marcas_id)],
        ]);

        $marca = Marcas::findOrFail($marcas_id);
        $marca->update($request->only('nome'));

        return redirect()->route('marcas.index')->with('success', 'Marca atualizada com sucesso.');
    }

    public function create()
    {
        return view('marcas.create');
    }

    public function show($marcas_id)
    {
        $marcas = Marcas::findOrFail($marcas_id);
        return view('marcas.show', compact('marcas'));
    }

    public function edit($marcas_id)
    {
        $marcas = Marcas::findOrFail($marcas_id);
        return view('marcas.edit', compact('marcas'));
    }
}
