<?php

namespace App\Http\Controllers;

use App\Models\Itens;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    // item_id
    public function index()
    {
        $itens = Itens::paginate(15)->withQueryString();
        return view('itens.index', compact('itens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:30|unique:itens,nome',
        ]);

        Itens::create($request->all());

        return redirect()->route('itens.index')->with('success', 'Item criado com sucesso.');
    }

    public function update(Request $request, $item_id)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:30', Rule::unique('itens', 'nome')->ignore($item_id)],
        ]);

        $itens = Itens::find($item_id);
        $itens->update($request->all());

        return redirect()->route('itens.index')->with('sucess', 'Item atualizado com sucesso.');
    }

    public function create()
    {
        return view('itens.create');
    }

    public function show($item_id)
    {
        $itens = Itens::find($item_id);

        return view('itens.show', compact('itens'));
    }

    public function edit($item_id)
    {
        $itens = Itens::find($item_id);

        return view('itens.edit', compact('itens'));
    }
}
