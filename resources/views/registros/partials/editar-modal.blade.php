<x-modal name="registro-editar" :show="false" maxWidth="3xl">
    <div x-data="editarRegistroModal()" x-on:editar-registro.window="open($event.detail.id)">

        {{-- HEADER --}}
        <div
            class="sticky top-0 z-10 flex items-center justify-between gap-3
                border-b border-gray-200 dark:border-gray-700
                bg-white dark:bg-gray-800 px-4 py-3">
            <h3 class="truncate text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                Editar registro <span x-text="titulo || ''"></span>
            </h3>
            <button @click="close()" class="rounded-md p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                aria-label="Fechar">✕</button>
        </div>

        {{-- BODY --}}
        <div class="p-3 sm:p-4 max-h-[85vh] overflow-y-auto">
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

@push('scripts')
    <script>
        window.editarRegistroModal = function() {
            return {
                loading: false,
                id: null,
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
                        const res = await fetch(`{{ url('/registros') }}/${id}/edit`, {
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

                        // MUITO IMPORTANTE: inicializar a assinatura após injetar o HTML
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
