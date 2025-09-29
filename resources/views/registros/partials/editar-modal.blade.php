<x-modal name="registro-editar" :show="false" maxWidth="3xl">
    <div x-data="editarRegistroModal()" x-on:editar-registro.window="open($event.detail.id)"
        x-on:open-modal.window="if ($event.detail === 'registro-editar') isOpen = true"
        x-on:close-modal.window="if (!$event.detail || $event.detail === 'registro-editar') isOpen = false"
        x-on:click.outside="close()" x-on:keydown.escape.window="isOpen = false; close()"
        data-edit-url="{{ url('/registros') }}">

        {{-- HEADER --}}
        <div
            class="sticky top-0 z-10 flex items-center justify-between gap-2 lg:gap-3
         border-b border-gray-200 dark:border-gray-700
         bg-white dark:bg-gray-800
         px-3 py-2 pt-[env(safe-area-inset-top)]
         sm:px-4 sm:py-3 lg:px-6 lg:py-4 shadow-sm">
            <h3 class="truncate text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                Editar registro <span x-text="titulo || ''"></span>
            </h3>

            {{-- área de toque maior pro X no iOS --}}
            <button @click="close()"
                class="rounded-md p-3 -m-1 text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300
                 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-gray-600
                 min-w-10 min-h-10 touch-manipulation"
                aria-label="Fechar">✕</button>
        </div>

        {{-- BODY --}}
        <div x-noscroll="isOpen"
            class="p-2 sm:p-4 lg:p-6
            max-h-[85dvh] sm:max-h-[70vh] lg:max-h-[80vh]
            overflow-y-auto overscroll-contain
            pb-[env(safe-area-inset-bottom)]
            bg-white dark:bg-gray-900 modal-edit-body">
            <template x-if="loading">
                <div class="space-y-3">
                    <div class="h-5 w-48 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                    <div class="h-96 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                </div>
            </template>

            <div x-ref="container" x-show="!loading"></div>
        </div>
    </div>
</x-modal>

{{-- Somente ajustes visuais (dark-mode e assinatura branca) --}}
<style>
    /* Força fundo branco onde a assinatura é exibida para manter contraste no modo escuro */
    .modal-edit-body img[alt*="Assinatura"],
    .modal-edit-body .assinatura-atual img,
    .modal-edit-body .signature-pad canvas,
    .modal-edit-body canvas[data-role="signature"],
    .modal-edit-body canvas.signature-pad,
    .modal-edit-body .signature canvas {
        background: #ffffff !important;
    }

    /* Bordas e textos dos blocos de mídia/assinatura em dark */
    @media (prefers-color-scheme: dark) {

        .modal-edit-body .border,
        .modal-edit-body img,
        .modal-edit-body canvas {
            border-color: rgb(55 65 81 / 1) !important;
            /* dark:border-gray-700 */
        }
    }
</style>

@push('scripts')
    <script>
        window.editarRegistroModal = function() {
            return {
                loading: false,
                id: null,
                isOpen: false,
                titulo: null,
                _abort: null,

                async open(id) {
                    this.id = id;
                    this.titulo = '#' + id;
                    this.loading = true;
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: 'registro-editar'
                    }));

                    if (this._abort) {
                        this._abort.abort();
                        this._abort = null;
                    }
                    this._abort = new AbortController();

                    try {
                        const base = this.$root?.dataset?.editUrl || '';
                        const res = await fetch(`${base}/${id}/edit`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            },
                            credentials: 'same-origin',
                            signal: this._abort.signal
                        });
                        if (!res.ok) throw new Error('Erro ' + res.status);
                        const html = await res.text();
                        this.$refs.container.innerHTML = html;

                        this.$nextTick(() => {
                            // aguarda um frame para o modal “abrir” e ter largura > 0
                            requestAnimationFrame(() => window.initSignaturePad?.(this.$refs.container));
                        });

                        // inicializa a assinatura após injetar o HTML (se existir)
                        if (window.initSignaturePad) window.initSignaturePad(this.$refs.container);

                        this.$nextTick(() => this.$refs.container.querySelector('input,select,textarea')?.focus());
                    } catch (e) {
                        if (e.name !== 'AbortError') {
                            alert('Não foi possível carregar o formulário.');
                            this.close();
                        }
                    } finally {
                        this.loading = false;
                        this._abort = null;
                    }
                },

                close() {
                    window.dispatchEvent(new CustomEvent('close-modal', {
                        detail: 'registro-editar'
                    }));
                    if (this._abort) {
                        this._abort.abort();
                        this._abort = null;
                    }
                    this.loading = false;
                    this.id = null;
                    this.titulo = null;
                    if (this.$refs.container) this.$refs.container.innerHTML = '';
                }
            }
        }
    </script>
@endpush
