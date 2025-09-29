{{-- <x-modal> é um componente Blade/Breeze --}}
{{-- name="registro-detalhes" é o identificador que o JS usa pra abrir/fechar --}}
{{-- :show="false" liga uma prop booleana; maxWidth controla a largura --}}
<x-modal name="registro-detalhes" :show="false" maxWidth="2xl">
    <div x-data="registroDetalhesModal()" x-on:abrir-registro.window="open($event.detail.id)"
        x-on:open-modal.window="if ($event.detail === 'registro-detalhes') isOpen = true"
        x-on:close-modal.window="if (!$event.detail || $event.detail === 'registro-detalhes') isOpen = false"
        x-on:click.outside="close()" x-on:keydown.escape.window="isOpen = false">
        {{-- HEADER sticky (com safe-area no iOS) --}}
        <div
            class="sticky top-0 z-10 flex items-center justify-between gap-2 lg:gap-3
       border-b border-gray-200 dark:border-gray-700
       bg-white dark:bg-gray-800 px-3 py-2 pt-[env(safe-area-inset-top)]
       sm:px-4 sm:py-3 sm:mt-5 lg:px-6 lg:py-4">
            <div class="min-w-0">
                <h3 class="truncate text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100"
                    x-text="modal.placa || '—'"></h3>
                <p class="truncate text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">
                    <span x-text="modal.marca || '—'"></span> —
                    <span x-text="modal.modelo || '—'"></span> •
                    <span x-text="modal.tipo === 'carro' ? 'Carro' : (modal.tipo === 'moto' ? 'Moto' : '—')"></span> •
                    <span x-text="modal.no_patio ? 'No pátio' : 'Saiu'"></span> •
                </p>
            </div>
            <button @click="close()" class="rounded-md p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                aria-label="Fechar">✕</button>
        </div>

        {{-- BODY: rola só aqui; bloqueia scroll do fundo com x-noscroll --}}
        <div x-noscroll="isOpen"
            class="p-2 sm:p-6 lg:p-8
       max-h-[85dvh] sm:max-h-[65vh] lg:max-h-[80vh]
       overflow-y-auto overscroll-contain space-y-4 lg:space-y-6">
            {{-- SKELETON de carregamento (placeholder animado) --}} <template x-if="modal.loading">
                <div class="space-y-4 sm:space-y-6">
                    <div class="aspect-video w-full rounded-lg bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="h-4 w-40 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="h-24 sm:h-28 lg:h-32 w-auto rounded border object-contain dark:border-gray-700"></div>
                </div>
            </template>

            {{-- CONTEÚDO real depois do fetch --}}
            <template x-if="!modal.loading">
                <div class="space-y-6 sm:space-y-8">

                    {{-- Carrossel principal (contain) + contador (slide / total) --}}
                    <div x-show="modal.slides?.length"
                        class="relative mx-auto rounded-lg bg-black overflow-hidden
                        aspect-video
                        w-full
                        md:max-w-3xl          /* ~768px */
                        lg:max-w-[900px]      /* ~900px no desktop */
                        xl:max-w-[1000px]     /* ~1000px em telas maiores */
                        2xl:max-w-[1100px]    /* limite superior opcional */
                        max-h-[62vh]          /* nunca passa 62% da altura */
                        lg:max-h-[58vh]       /* ainda menor em desktop */">
                        <img :src="modal.slides?.[slideIdx]?.url_full || modal.slides?.[slideIdx]?.url || ''"
                            class="absolute inset-0 h-full w-full object-contain select-none" loading="eager"
                            decoding="async" fetchpriority="high" />
                        <div
                            class="absolute left-2 top-2 rounded-full bg-black/70 px-2.5 py-0.5 text-xs font-medium text-white">
                            <span x-text="modal.slides?.[slideIdx]?.label || '—'"></span>
                            <span class="opacity-75">—</span>
                            <span x-text="(slideIdx + 1) + ' / ' + modal.slides.length"></span>
                        </div>
                        <button type="button" @click="prev()"
                            class="absolute left-2  top-1/2 -translate-y-1/2 rounded-full bg-white/85 px-3 py-1.5 text-lg">‹</button>
                        <button type="button" @click="next()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/85 px-3 py-1.5 text-lg">›</button>
                    </div>

                    {{-- Mini-carrossel com vizinhos (prev | atual | next) para navegação rápida --}}
                    <template x-if="modal.slides?.length">
                        <div class="mt-3 flex items-center justify-center gap-3 sm:gap-2.5">
                            <template x-for="i in neighbors(slideIdx)" :key="'thumb-' + i">
                                <button type="button"
                                    class="relative w-20 h-20 sm:w-28 sm:h-28 lg:w-32 lg:h-32 shrink-0 rounded overflow-hidden ring-2 transition"
                                    :class="i === idxWrap(slideIdx) ?
                                        'ring-indigo-500 scale-[1.06]' :
                                        'ring-transparent opacity-80 hover:ring-gray-300 scale-95'"
                                    @click="go(i)">
                                    <img class="absolute inset-0 w-full h-full object-cover select-none"
                                        :alt="modal.slides[i]?.label || ''" :src="modal.slides[i]?.url || _PH"
                                        loading="lazy" decoding="async">
                                </button>
                            </template>
                        </div>
                    </template>

                    {{-- Detalhes em grid com fallback "—" quando ausente --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 lg:gap-4">
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Responsável
                            </div>
                            <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                x-text="modal.user || '—'">
                            </div>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Placa</div>
                            <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                x-text="modal.placa || '—'">
                            </div>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Marca</div>
                            <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                x-text="modal.marca || '—'">
                            </div>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Modelo</div>
                            <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                x-text="modal.modelo || '—'">
                            </div>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</div>
                            <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                x-text="modal.tipo === 'carro' ? 'Carro' : (modal.tipo === 'moto' ? 'Moto' : '—')">
                            </div>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Status</div>
                            <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                x-text="modal.no_patio ? 'No pátio' : 'Saiu'"></div>
                        </div>
                    </div>

                    {{-- Itens do veículo (chips) --}}
                    <template x-if="modal.itens?.length">
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Itens do
                                veículo</div>
                            <div class="mt-1 flex flex-wrap gap-2">
                                <template x-for="(nome, idx) in modal.itens" :key="idx">
                                    <span
                                        class="rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-[11px] sm:text-xs text-gray-700 dark:text-gray-200"
                                        x-text="nome"></span>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Observação (preserva quebras com whitespace-pre-line) --}}
                    <template x-if="modal.observacao">
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Observação
                            </div>
                            <div class="whitespace-pre-line text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                x-text="modal.observacao"></div>
                        </div>
                    </template>

                    <!-- Assinatura + dados do reboque lado a lado -->
                    <template x-if="modal.assinatura || modal.reboque_placa || modal.reboque_condutor">
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">
                                Reboque
                            </div>

                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 lg:gap-6 items-start">
                                <!-- Coluna 1: Condutor do reboque -->
                                <div class="space-y-1">
                                    <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Condutor do reboque
                                    </div>
                                    <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                        x-text="modal.reboque_condutor || '—'"></div>
                                </div>

                                <!-- Coluna 2: Placa do reboque -->
                                <div class="space-y-1">
                                    <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Placa do reboque
                                    </div>
                                    <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                        x-text="modal.reboque_placa || '—'"></div>
                                </div>

                                <!-- Coluna 3: Imagem da assinatura -->
                                <div class="sm:justify-self-end">
                                    <template x-if="modal.assinatura">
                                        <img :src="modal.assinatura"
                                            class="h-28 w-auto rounded border object-contain dark:border-gray-700"
                                            alt="Assinatura do reboque">
                                    </template>
                                    <template x-if="!modal.assinatura">
                                        <div
                                            class="h-28 w-full sm:w-40 rounded border border-dashed dark:border-gray-700
                      flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">
                                            Sem assinatura
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
</x-modal>
</div>

{{-- @push('scripts') para injetar JS desta partial no @stack('scripts') do layout --}}
@push('scripts')
    <script>
        // Factory GLOBAL que o Alpine usa para criar/gerenciar o estado do modal
        window.registroDetalhesModal = function() {
            return {
                // estado
                isOpen: false,
                modal: {
                    loading: false,
                    slides: []
                },
                slideIdx: 0,
                _abort: null,
                _preloaded: new Set(),
                _visible: new Set(),
                _PH: 'data:image/gif;base64,R0lGODlhAQABAAAAACw=',

                // helpers de índice/carrossel
                idxWrap(i) {
                    const n = this.modal.slides?.length || 0;
                    return n ? ((i % n) + n) % n : 0;
                },
                neighbors(i) {
                    const n = this.modal.slides?.length || 0;
                    if (!n) return [];
                    if (n === 1) return [0, 0, 0];
                    const cur = this.idxWrap(i);
                    return [this.idxWrap(cur - 1), cur, this.idxWrap(cur + 1)];
                },

                // --- pré-carregamento de imagens para UX melhor ---
                preload(i) {
                    if (!this.modal.slides?.[i] || this._preloaded.has(i)) return;
                    const img = new Image();
                    img.decoding = 'async';
                    img.src = this.modal.slides[i].url;
                    this._preloaded.add(i);
                    this._visible.add(i);
                },
                center(i) {
                    const [p, c, n] = this.neighbors(i);
                    this._visible = new Set([p, c, n]);
                    this.preload(p);
                    this.preload(c);
                    this.preload(n);
                },

                // navegação
                go(i) {
                    this.slideIdx = this.idxWrap(i);
                    this.center(this.slideIdx);
                },
                prev() {
                    if (!this.modal.slides?.length) return;
                    this.slideIdx = this.idxWrap(this.slideIdx - 1);
                    this.center(this.slideIdx);
                },
                next() {
                    if (!this.modal.slides?.length) return;
                    this.slideIdx = this.idxWrap(this.slideIdx + 1);
                    this.center(this.slideIdx);
                },

                // ciclo de vida

                reset() {
                    this.modal = {
                        loading: false,
                        slides: []
                    };
                    this.slideIdx = 0;
                    this._preloaded = new Set();
                    this._visible = new Set();
                },

                async open(id) {
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: 'registro-detalhes'
                    }));
                    this.isOpen = true; // <— LIGA o noscroll

                    if (this._abort) {
                        this._abort.abort();
                        this._abort = null;
                    }
                    this.reset();
                    this.modal.loading = true;

                    this._abort = new AbortController();
                    try {
                        const res = await fetch(`{{ url('/registros') }}/${id}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin',
                            signal: this._abort.signal
                        });
                        if (!res.ok) throw new Error(`Erro ${res.status}`);
                        const data = await res.json();
                        this.modal = {
                            ...data,
                            loading: false
                        };
                        this.slideIdx = 0;
                        this.center(this.slideIdx);
                    } catch (e) {
                        if (e.name !== 'AbortError') {
                            console.error(e);
                            this.modal.loading = false;
                            alert('Não foi possível carregar os detalhes.');
                            this.close();
                        }
                    } finally {
                        this._abort = null;
                    }
                },


                close() {
                    window.dispatchEvent(new CustomEvent('close-modal', {
                        detail: 'registro-detalhes'
                    }));
                    this.isOpen = false; // <— DESLIGA o noscroll

                    if (this._abort) {
                        this._abort.abort();
                        this._abort = null;
                    }
                    this.reset();
                },
            };
        };
    </script>
@endpush
