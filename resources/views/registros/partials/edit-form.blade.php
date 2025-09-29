<form method="POST"
      action="{{ route('registros.update', $registro) }}"
      enctype="multipart/form-data"
      x-data="{ tipo: @js(old('tipo', $registro->tipo)) }"
      class="space-y-6">

    @csrf
    @method('PUT')

    @if ($errors->any())
        <div
            class="rounded-md border px-4 py-3 mb-4
                   bg-red-100 text-red-800 border-red-200
                   dark:bg-red-900/30 dark:text-red-100 dark:border-red-800">
            <strong class="font-bold">Opa! Encontramos alguns erros:</strong>
            <ul class="mt-2 list-disc pl-6 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Campos básicos (placa, tipo, marca, etc.) --}}
    @include('registros._form', [
        'marcas'   => $marcas,
        'itens'    => $itens,
        'registro' => $registro,  // permite preencher os valores no _form
    ])

    {{-- Assinatura e fotos (reutiliza o mesmo bloco do create) --}}
    @include('registros._media', ['registro' => $registro])

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button"
                @click="window.dispatchEvent(new CustomEvent('close-modal', { detail:'registro-editar' }))"
                class="inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm font-medium
                       text-gray-700 hover:bg-gray-50 border-gray-300
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                       dark:text-gray-200 dark:hover:bg-gray-800 dark:border-gray-700
                       dark:focus:ring-indigo-400 dark:focus:ring-offset-gray-900">
            Cancelar
        </button>

        <x-primary-button type="submit"
            class="justify-center
                   focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                   dark:focus:ring-indigo-400 dark:focus:ring-offset-gray-900">
            Salvar alterações
        </x-primary-button>
    </div>
</form>
