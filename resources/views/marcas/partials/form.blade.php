<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div>
        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">Nome</label>
        <input name="nome" type="text" value="{{ old('nome', $marca->nome ?? '') }}"
               class="w-full rounded border px-3 py-2
                      bg-white dark:bg-gray-900
                      text-gray-900 dark:text-gray-100
                      placeholder-gray-400 dark:placeholder-gray-500
                      border-gray-300 dark:border-gray-600
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
               required>
        @error('nome')
            <small class="text-red-600 dark:text-red-400">{{ $message }}</small>
        @enderror
    </div>

    {{-- Exemplo de outro campo opcional: --}}
    {{-- 
    <div>
        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">Descrição</label>
        <textarea name="descricao" rows="3"
                  class="w-full rounded border px-3 py-2
                         bg-white dark:bg-gray-900
                         text-gray-900 dark:text-gray-100
                         placeholder-gray-400 dark:placeholder-gray-500
                         border-gray-300 dark:border-gray-600
                         focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">{{ old('descricao', $marca->descricao ?? '') }}</textarea>
    </div> 
    --}}

    <div class="flex justify-end gap-2">
        <x-secondary-button type="button" x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
        <x-primary-button type="submit">Salvar</x-primary-button>
    </div>
</form>
