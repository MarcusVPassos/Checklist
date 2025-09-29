<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Registros</h2>
            <div class="flex gap-2">
                @can('registros.create')
                    <a href="{{ route('registros.create') }}"
                        class="hidden sm:inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white shadow
                          hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                          dark:bg-indigo-500 dark:hover:bg-indigo-600 dark:focus:ring-indigo-400 dark:focus:ring-offset-gray-900">
                        Novo Registro
                    </a>
                @endcan
                @canany(['registros.restore', 'registros.force-delete'])
                    <a href="{{ route('registros.trashed') }}"
                        class="hidden sm:inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white shadow
                          hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                          dark:bg-indigo-500 dark:hover:bg-indigo-600 dark:focus:ring-indigo-400 dark:focus:ring-offset-gray-900">
                        Lixeira
                    </a>
                @endcanany
            </div>
        </div>
    </x-slot>

    <div class="py-4" x-data="registrosPage('{{ route('registros.index') }}', @js($registros->nextCursor()?->encode() ?? null))">

        <div class="p-4 sm:px-2 lg:px-5 space-y-3">
            {{-- atalhos visíveis só no mobile --}}
            <div class="flex w-full gap-2">
                @can('registros.create')
                    <a href="{{ route('registros.create') }}"
                        class="md:hidden p-3 text-center rounded-md bg-indigo-600 text-white text-md w-full
                          hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                          dark:bg-indigo-500 dark:hover:bg-indigo-600 dark:focus:ring-indigo-400 dark:focus:ring-offset-gray-900">
                        Novo
                    </a>
                @endcan
                @canany(['registros.restore', 'registros.force-delete'])
                    <a href="{{ route('registros.trashed') }}"
                        class="md:hidden p-3 text-center rounded-md bg-indigo-600 text-white text-md w-full
                          hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                          dark:bg-indigo-500 dark:hover:bg-indigo-600 dark:focus:ring-indigo-400 dark:focus:ring-offset-gray-900">
                        Lixeira
                    </a>
                @endcanany
            </div>

            {{-- ========== FILTROS ========== --}}
            <div @toggle-filtros.window="open = !open" x-data="{ open: false }">
                {{-- MOBILE: dropdown/accordion --}}
                <div class="md:hidden p-5">
                    <button type="button"
                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3
                               flex items-center justify-between p-4
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                        @click="open = !open" aria-controls="mobileFilterPanel" :aria-expanded="open.toString()">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Filtros</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="mobileFilterPanel" x-show="open" x-transition x-cloak class="mt-2">
                        {{-- IMPORTANTÍSSIMO: partial já vem com x-data; passe as coleções --}}
                        @include('registros.partials._filtros-form', [
                            'modo' => 'mobile',
                            'marcas' => $marcas,
                            'itens' => $itens,
                            'usuarios' => $usuarios,
                            'anos' => $anos,
                            'mesesPorAno' => $mesesPorAno,
                        ])
                    </div>
                </div>

                {{-- DESKTOP/TABLET: filtros sempre visíveis --}}
                <div
                    class="hidden md:block rounded-xl border border-gray-200/60 dark:border-gray-700/60
                           bg-white/80 dark:bg-gray-800/80 backdrop-blur p-4">
                    @include('registros.partials._filtros-form', [
                        'modo' => 'desktop',
                        'marcas' => $marcas,
                        'itens' => $itens,
                        'usuarios' => $usuarios,
                        'anos' => $anos,
                        'mesesPorAno' => $mesesPorAno,
                    ])
                </div>
            </div>
            {{-- ========== /FILTROS ========== --}}

            {{-- GRID dos cards (responsivo) --}}
            <div id="cardsGrid"
                class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 w-full">
                @include('registros.cards', ['registros' => $registros])
            </div>

            @include('registros.partials.detalhes-modal')
            @include('registros.partials.editar-modal')

            {{-- Botão Carregar mais (cursor) --}}
            <div class="mt-6 flex justify-center" x-show="nextCursor">
                <button @click="loadMore()"
                    class="rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100
                           hover:bg-gray-300 dark:hover:bg-gray-600
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                           dark:focus:ring-indigo-400 dark:focus:ring-offset-gray-900
                           disabled:opacity-50"
                    :disabled="loading">
                    <span x-show="!loading">Carregar mais</span>
                    <span x-show="loading">Carregando...</span>
                </button>
            </div>

            @if ($registros->nextCursor())
                <div data-next-cursor="{{ $registros->nextCursor()->encode() }}"></div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            // ====== Página (cursor + preservação de filtros) ======
            document.addEventListener('alpine:init', () => {
                Alpine.data('registrosPage', (baseUrl, initialCursor) => ({
                    nextCursor: initialCursor,
                    loading: false,
                    async loadMore() {
                        if (!this.nextCursor || this.loading) return;
                        this.loading = true;
                        try {
                            const qs = window.location.search;
                            const join = qs ? `${qs}&` : '?';
                            const url =
                            `${baseUrl}${join}cursor=${encodeURIComponent(this.nextCursor)}`;
                            const res = await fetch(url, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const html = await res.text();
                            const doc = new DOMParser().parseFromString(html, 'text/html');
                            doc.querySelectorAll('#cardsGrid > *').forEach(el =>
                                document.querySelector('#cardsGrid').appendChild(el)
                            );
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
</x-app-layout>
