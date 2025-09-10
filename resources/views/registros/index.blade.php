{{-- resources/views/registros/index.blade.php --}}
<x-app-layout>
    <x-slot name="header"> {{-- SLOT nomeado "Header" do componente <x-app-layout> --}}
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Registros</h2>
            <a href="{{ route('registros.create') }}" {{-- Helper de rota. Gera URL por nome da rota --}}
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white shadow hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                Novo Registro
            </a>
        </div>
    </x-slot>
    {{-- Alpine component "registrosPage": recebe a URL base e o cursor inicial --}} // {{-- @js serializa o objeto PHP em JSON seguro para embed no JS --}}
    <div class="py-8" x-data="registrosPage('{{ route('registros.index') }}', @js($registros->nextCursor()?->encode() ?? null))">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <template x-if="flash"> {{-- Exibe flash (mensagem efêmera) quando existir (Alpine) --}}
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-200"
                    x-text="flash"></div>
            </template>

            {{-- GRID principal dos cards. Mantemos um #cardsGrid para anexar novos cards via fetch + DOMParser --}}
            <div id="cardsGrid" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {{-- PARTIAL simples: inclui a subview "registros.cards" e passa $registros --}}
                {{-- Use @include para fragmentos “burros” (dumb partials). Quando ganhar API/slots, pensar em <x-...>. --}}
                @include('registros.cards', ['registros' => $registros])
            </div>

            @include('registros.partials.detalhes-modal') {{-- Partial do MODAL de detalhes (um componente maior/alpine) --}}

            {{-- Botão "Carregar mais": aparece se houver "nextCursor" (cursor pagination) --}}
            <div class="mt-6 flex justify-center" x-show="nextCursor">
                <button @click="loadMore()"
                    class="rounded-md bg-gray-200 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50"
                    :disabled="loading">
                    <span x-show="!loading">Carregar mais</span>
                    <span x-show="loading">Carregando...</span>
                </button>
            </div>

            @push('scripts')
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('registrosPage', (baseUrl, initialCursor) => ({
                        nextCursor: initialCursor,
                        loading: false,
                        flash: null,
    
                        async loadMore() {
                            if (!this.nextCursor || this.loading) return;
                            this.loading = true;
                            try {
                                // Busca a próxima "página" usando cursor (server-side deve detectar X-Requested-With)
                                const url = `${baseUrl}?cursor=${encodeURIComponent(this.nextCursor)}`;
                                const res = await fetch(url, {
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                                // Recebe HTML parcial e “mergeia” os novos cards no #cardsGrid (progressive enhancement)
                                const html = await res.text();
                                const doc = new DOMParser().parseFromString(html, 'text/html');
                                // Anexa cada card retornado
                                doc.querySelectorAll('#cardsGrid > *')
                                    .forEach(el => document.querySelector('#cardsGrid').appendChild(el));
                                // Atualiza o próximo cursor lendo o data-attribute renderizado pelo Blade do server
                                this.nextCursor = doc.querySelector('[data-next-cursor]')?.getAttribute(
                                    'data-next-cursor') || null;
                            } finally {
                                this.loading = false;
                            }
                        },
                    }));
                });
            </script>
        @endpush

        {{-- Expõe no HTML o próximo cursor (quando existir). O JS lê isso ao concatenar novas páginas. --}}
        @if ($registros->nextCursor())
            <div data-next-cursor="{{ $registros->nextCursor()->encode() }}"></div>
        @endif
</x-app-layout>
