<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div>
        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">Nome</label>
        <input name="nome" type="text" value="{{ old('nome', $item->nome ?? '') }}"
               class="w-full rounded border px-3 py-2 bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600"
               required>
        @error('nome')
            <small class="text-red-600">{{ $message }}</small>
        @enderror
    </div>

    <div class="flex justify-end gap-2">
        <x-secondary-button type="button" x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
        <x-primary-button type="submit">Salvar</x-primary-button>
    </div>
</form>
