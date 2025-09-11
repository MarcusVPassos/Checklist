<form method="POST"
      action="{{ route('registros.update', $registro) }}"
      enctype="multipart/form-data"
      x-data="{ tipo: @js(old('tipo', $registro->tipo)) }"
      class="space-y-6">

    @csrf
    @method('PUT')

    @if ($errors->any())
        <div style="background-color: #f8d7da; color: #721c24; padding: 1rem; border: 1px solid #f5c6cb; border-radius: .25rem; margin-bottom: 1rem;">
            <strong style="font-weight: bold;">Opa! Encontramos alguns erros:</strong>
            <ul style="margin-top: 0.5rem; padding-left: 1.5rem; list-style-type: disc;">
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

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end pt-4 border-t">
        <button type="button"
                @click="window.dispatchEvent(new CustomEvent('close-modal', { detail:'registro-editar' }))"
                class="inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Cancelar
        </button>

        <x-primary-button type="submit" class="justify-center">
            Salvar alterações
        </x-primary-button>
    </div>
</form>
