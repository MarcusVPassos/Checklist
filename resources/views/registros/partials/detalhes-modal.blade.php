{{-- <x-modal> é um componente Blade/Breeze --}}
{{-- name="registro-detalhes" é o identificador que o JS usa pra abrir/fechar --}}
{{-- :show="false" liga uma prop booleana; maxWidth controla a largura --}}
<x-modal name="registro-detalhes" :show="false" maxWidth="2xl">
    <div x-data="registroDetalhesModal()" {{-- cria a instância Alpine deste modal --}} 
            x-on:abrir-registro.window="open($event.detail.id)"> {{-- ouve o CustomEvent global e chama open(id) --}}
        {{-- HEADER fixo (sticky) pra manter título/botão durante scroll do body do modal --}}
        <div
            class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            <div class="min-w-0">
                <h3 class="truncate text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100"
                    x-text="modal.placa || '—'"></h3>
                <p class="truncate text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">
                    <span x-text="modal.marca || '—'"></span> —
                    <span x-text="modal.modelo || '—'"></span> •
                    <span x-text="modal.tipo === 'carro' ? 'Carro' : (modal.tipo === 'moto' ? 'Moto' : '—')"></span> •
                    <span x-text="modal.no_patio ? 'No pátio' : 'Saiu'"></span>
                </p>
            </div>
            {{-- Fecha o modal do Jetstream via evento padrão open-modal/close-modal --}}
            <button @click="close()" class="rounded-md p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                aria-label="Fechar">✕</button>
        </div>

        {{-- BODY com scroll interno e skeleton enquanto carrega --}}
        <div class="p-3 sm:p-4 max-h-[85vh] overflow-y-auto space-y-5">

            {{-- SKELETON de carregamento (placeholder animado) --}}
            <template x-if="modal.loading">
                <div class="space-y-4">
                    <div class="aspect-video w-full rounded-lg bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="h-4 w-40 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="h-24 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                </div>
            </template>

            {{-- CONTEÚDO real depois do fetch --}}
            <template x-if="!modal.loading">
                <div class="space-y-6">

                    {{-- Carrossel principal (contain) + contador (slide / total) --}}
                    <div x-show="modal.slides?.length"
                        class="relative aspect-video w-full rounded-lg bg-black overflow-hidden">
                        <img :src="modal.slides?.[slideIdx]?.url || ''" :alt="modal.slides?.[slideIdx]?.label || ''"
                            class="absolute inset-0 h-full w-full object-contain select-none" loading="eager">
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
                        <div class="mt-3 flex items-center justify-center gap-3">
                            <template x-for="i in neighbors(slideIdx)" :key="'thumb-' + i">
                                <button type="button"
                                    class="relative w-24 h-24 sm:w-28 sm:h-28 shrink-0 rounded overflow-hidden ring-2 transition"
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
                    <div class="grid grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Placa</div>
                            <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                x-text="modal.placa || '—'"></div>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Marca</div>
                            <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                x-text="modal.marca || '—'"></div>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Modelo</div>
                            <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                                x-text="modal.modelo || '—'"></div>
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

                    {{-- Assinatura --}}
                    <template x-if="modal.assinatura">
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Assinatura
                            </div>
                            <img :src="modal.assinatura"
                                class="mt-1 h-28 w-auto rounded border object-contain dark:border-gray-700"
                                alt="">
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
                    // Abre o modal do Breeze via evento "open-modal"
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: 'registro-detalhes'
                    }));
                    // Cancela requisição anterior (se houver)
                    if (this._abort) {
                        this._abort.abort();
                        this._abort = null;
                    }
                    this.reset();
                    this.modal.loading = true;

                    this._abort = new AbortController();
                    try {
                        // Busca detalhes do registro (JSON). GET seguro (sem CSRF); se fosse POST, envie o token CSRF.
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
                        
                        // Atualiza estado e centraliza o carrossel
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
                            window.dispatchEvent(new CustomEvent('close-modal', {
                                detail: 'registro-detalhes'
                            }));
                        }
                    } finally {
                        this._abort = null;
                    }
                },

                close() {
                    window.dispatchEvent(new CustomEvent('close-modal', {
                        detail: 'registro-detalhes'
                    }));
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
