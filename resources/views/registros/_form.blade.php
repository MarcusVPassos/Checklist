@php
    $isEdit = isset($registro);
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
    <div>
        <x-input-label for="placa" value="Placa" class="text-gray-700 dark:text-gray-300" />
        <x-text-input id="placa" name="placa" type="text" class="mt-1 block w-full uppercase tracking-wider
            border-gray-300 text-gray-900 placeholder-gray-400
            focus:border-indigo-500 focus:ring-indigo-500
            dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:border-gray-700
            dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
            maxlength="10" value="{{ old('placa', $registro->placa ?? '') }}" required />
        <x-input-error :messages="$errors->get('placa')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="tipo" value="Tipo" class="text-gray-700 dark:text-gray-300" />
        <select id="tipo" name="tipo" x-model="tipo"
            class="mt-1 block w-full rounded-md
                   border-gray-300 bg-white text-gray-900
                   focus:border-indigo-500 focus:ring-indigo-500
                   dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700
                   dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
            required>
            @foreach (['carro' => 'Carro', 'moto' => 'Moto'] as $v => $label)
                <option value="{{ $v }}" @selected(old('tipo', $registro->tipo ?? 'carro') === $v)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="marca_id" value="Marca" class="text-gray-700 dark:text-gray-300" />
        <select id="marca_id" name="marca_id"
            class="mt-1 block w-full rounded-md
                   border-gray-300 bg-white text-gray-900
                   focus:border-indigo-500 focus:ring-indigo-500
                   dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700
                   dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
            required>
            <option value="" disabled {{ old('marca_id', $registro->marca_id ?? null) ? '' : 'selected' }}>
                Selecione…
            </option>
            @foreach ($marcas as $m)
                <option value="{{ $m->id }}" @selected((int) old('marca_id', $registro->marca_id ?? 0) === $m->id)>{{ $m->nome }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('marca_id')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mt-5">
    <div class="sm:col-span-2">
        <x-input-label for="modelo" value="Modelo" class="text-gray-700 dark:text-gray-300" />
        <x-text-input id="modelo" name="modelo" type="text" class="mt-1 block w-full
            border-gray-300 text-gray-900 placeholder-gray-400
            focus:border-indigo-500 focus:ring-indigo-500
            dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:border-gray-700
            dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
            value="{{ old('modelo', $registro->modelo ?? '') }}" required />
        <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
    </div>

    <div class="flex items-end">
        <input type="hidden" name="no_patio" value="0">
        <label class="inline-flex items-center gap-2 text-gray-700 dark:text-gray-300">
            <input id="no_patio" type="checkbox" name="no_patio" value="1" @checked((bool) old('no_patio', $registro->no_patio ?? true))
                class="rounded border-gray-300 text-indigo-600
                       focus:ring-indigo-500 dark:focus:ring-indigo-400
                       dark:border-gray-700 dark:bg-gray-900">
            <span>No pátio</span>
        </label>
        <x-input-error :messages="$errors->get('no_patio')" class="mt-2" />
    </div>
</div>

<div class="mt-5">
    <x-input-label for="observacao" value="Observação" class="text-gray-700 dark:text-gray-300" />
    <textarea id="observacao" name="observacao" rows="3"
        class="mt-1 block w-full rounded-md
               border-gray-300 text-gray-900 placeholder-gray-400
               focus:border-indigo-500 focus:ring-indigo-500
               dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:border-gray-700
               dark:focus:border-indigo-400 dark:focus:ring-indigo-400">{{ old('observacao', $registro->observacao ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('observacao')" class="mt-2" />
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 mt-5">
    <div>
        <x-input-label for="reboque_condutor" value="Condutor do Reboque" class="text-gray-700 dark:text-gray-300" />
        <x-text-input id="reboque_condutor" name="reboque_condutor" type="text" class="mt-1 block w-full
            border-gray-300 text-gray-900 placeholder-gray-400
            focus:border-indigo-500 focus:ring-indigo-500
            dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:border-gray-700
            dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
            value="{{ old('reboque_condutor', $registro->reboque_condutor ?? '') }}" required />
        <x-input-error :messages="$errors->get('reboque_condutor')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="reboque_placa" value="Placa do Reboque" class="text-gray-700 dark:text-gray-300" />
        <x-text-input id="reboque_placa" name="reboque_placa" type="text" class="mt-1 block w-full uppercase
            border-gray-300 text-gray-900 placeholder-gray-400
            focus:border-indigo-500 focus:ring-indigo-500
            dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:border-gray-700
            dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
            value="{{ old('reboque_placa', $registro->reboque_placa ?? '') }}" required />
        <x-input-error :messages="$errors->get('reboque_placa')" class="mt-2" />
    </div>
</div>

<div class="mt-6">
    <x-input-label value="Itens verificados" class="text-gray-700 dark:text-gray-300" />
    @php
        $marcados = collect(old('itens', $isEdit ? $registro->itens->pluck('id')->all() : []))
            ->map(fn($v) => (int) $v)
            ->all();
    @endphp
    <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3">
        @foreach ($itens as $item)
            <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="itens[]" value="{{ $item->id }}" @checked(in_array($item->id, $marcados, true))
                    class="rounded border-gray-300 text-indigo-600
                           focus:ring-indigo-500 dark:focus:ring-indigo-400
                           dark:border-gray-700 dark:bg-gray-900">
                <span>{{ $item->nome }}</span>
            </label>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('itens')" class="mt-2" />
    <x-input-error :messages="$errors->get('itens.*')" class="mt-2" />
</div>
