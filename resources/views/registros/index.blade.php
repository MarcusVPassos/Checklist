<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight ">
                    {{ __('Registros') }}
                </h2>
                {{-- <div class="text-sm text-gray-600 dark:text-gray-300">
                    Total: <strong>{{ $registros->total() }}</strong>
                </div> --}}
            </div>
            <div class="flex items-center justify-between">
                <a href="{{ route('registros.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white shadow hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                    Novo Registro
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Raiz com Alpine store usando o mesmo padrão do Breeze --}}
    <div class="py-8" x-data x-init="if (!Alpine.store('regUI')) {
        Alpine.store('regUI', {
            slide: 0,
            data: {
                id: null,
                placa: '',
                tipo: '',
                no_patio: false,
                marca: '',
                modelo: '',
                observacao: null,
                reboque_condutor: '',
                reboque_placa: '',
                assinatura: null,
                itens: [],
                slides: []
            },
            open(payload) {
                this.data = payload;
                this.slide = 0;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'registro-detalhes' }));
            },
            close() {
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'registro-detalhes' }));
            },
            prev() {
                if (!this.data.slides.length) return;
                this.slide = (this.slide - 1 + this.data.slides.length) % this.data.slides.length;
            },
            next() {
                if (!this.data.slides.length) return;
                this.slide = (this.slide + 1) % this.data.slides.length;
            },
        });
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div
                    class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            {{-- GRID DE CARDS --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $rotulos = [
                        'frente' => 'Frente',
                        'lado_direito' => 'Lado direito',
                        'lado_esquerdo' => 'Lado esquerdo',
                        'traseira' => 'Traseira',
                        'capo_aberto' => 'Capô aberto',
                        'numero_do_motor' => 'Número do motor',
                        'painel_lado_direito' => 'Painel (lado direito)',
                        'painel_lado_esquerdo' => 'Painel (lado esquerdo)',
                        'bateria_carro' => 'Bateria (carro)',
                        'chave_carro' => 'Chave (carro)',
                        'estepe_do_veiculo' => 'Estepe',
                        'motor_lado_direito' => 'Motor (lado direito)',
                        'motor_lado_esquerdo' => 'Motor (lado esquerdo)',
                        'painel_moto' => 'Painel (moto)',
                        'chave_moto' => 'Chave (moto)',
                        'bateria_moto' => 'Bateria (moto)',
                    ];
                @endphp

                @forelse ($registros as $r)
                    @php
                        $capa =
                            $r->tipo === 'carro'
                                ? $r->imagens->firstWhere('posicao', 'frente')
                                : $r->imagens->firstWhere('posicao', 'motor_lado_direito');
                        $capa ??= $r->imagens->first();

                        $capaUrl = $capa
                            ? asset('storage/' . $capa->path)
                            : 'https://via.placeholder.com/640x360?text=Sem+Foto';

                        $payload = [
                            'id' => $r->id,
                            'placa' => $r->placa,
                            'tipo' => $r->tipo,
                            'no_patio' => (bool) $r->no_patio,
                            'marca' => $r->marca?->nome,
                            'modelo' => $r->modelo,
                            'observacao' => $r->observacao,
                            'reboque_condutor' => $r->reboque_condutor,
                            'reboque_placa' => $r->reboque_placa,
                            'assinatura' => $r->assinatura_path ? asset('storage/' . $r->assinatura_path) : null,
                            'itens' => $r->itens->pluck('nome')->values(),
                            'slides' => $r->imagens
                                ->map(
                                    fn($img) => [
                                        'url' => asset('storage/' . $img->path),
                                        'posicao' => $img->posicao,
                                        'label' => $rotulos[$img->posicao] ?? $img->posicao,
                                    ],
                                )
                                ->values(),
                        ];
                    @endphp

                    <div
                        class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700">
                        <button type="button" class="block w-full text-left"
                            @click="Alpine.store('regUI').open(@js($payload))">
                            <img src="{{ $capaUrl }}" class="h-48 w-full object-cover" alt="Capa"
                                loading="lazy">
                            <div class="p-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $r->placa }}</h3>
                                    @if ($r->no_patio)
                                        <span
                                            class="rounded-full bg-green-100 dark:bg-green-900/40 px-2 py-1 text-xs font-medium text-green-800 dark:text-green-200">No
                                            pátio</span>
                                    @else
                                        <span
                                            class="rounded-full bg-gray-200 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-800 dark:text-gray-200">Saiu</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $r->marca?->nome }} —
                                    {{ $r->modelo }}</p>

                                @if ($r->imagens->count())
                                    <div class="mt-3 flex -space-x-2 overflow-hidden">
                                        @foreach ($r->imagens->take(5) as $img)
                                            <img src="{{ asset('storage/' . $img->path) }}"
                                                class="inline-block h-8 w-8 rounded ring-2 ring-white object-cover"
                                                alt="" loading="lazy">
                                        @endforeach
                                        @if ($r->imagens->count() > 5)
                                            <span
                                                class="inline-flex h-8 w-8 items-center justify-center rounded bg-gray-100 dark:bg-gray-700 text-xs text-gray-600 dark:text-gray-300 ring-2 ring-white">
                                                +{{ $r->imagens->count() - 5 }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <div class="mt-4">
                                    <span class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">Ver
                                        detalhes</span>
                                </div>
                            </div>
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-gray-600 dark:text-gray-300">Sem registros ainda.</p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $registros->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL: usando o mesmo componente Breeze que você enviou --}}
    {{-- Dica: se seu x-modal aceitar só até 2xl, mantém; se você ampliar, pode trocar para 3xl/4xl/6xl --}}
    <x-modal name="registro-detalhes" :show="false" maxWidth="2xl">
        {{-- Header (fixo no topo do modal) --}}
        <div
            class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
            <div class="min-w-0">
                <h3 class="truncate text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100"
                    x-text="Alpine.store('regUI').data.placa"></h3>
                <p class="truncate text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">
                    <span x-text="Alpine.store('regUI').data.marca ?? '—'"></span> —
                    <span x-text="Alpine.store('regUI').data.modelo"></span> •
                    <span x-text="Alpine.store('regUI').data.tipo === 'carro' ? 'Carro' : 'Moto'"></span> •
                    <span x-text="Alpine.store('regUI').data.no_patio ? 'No pátio' : 'Saiu'"></span>
                </p>
            </div>
            <button @click="Alpine.store('regUI').close()"
                class="rounded-md p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                aria-label="Fechar">✕</button>
        </div>

        {{-- Conteúdo rolável do modal --}}
        <div class="p-3 sm:p-4 max-h-[85vh] overflow-y-auto space-y-5"
            x-on:keydown.arrow-left.window="Alpine.store('regUI').prev()"
            x-on:keydown.arrow-right.window="Alpine.store('regUI').next()">

            {{-- ===== CARROSSEL RESPONSIVO ===== --}}
            {{-- ===== CARROSSEL COM ZOOM ===== --}}
            <div class="w-full" x-data="{
                get slides() { return Alpine.store('regUI').data.slides || [] },
                get idx() { return Alpine.store('regUI').slide },
                set idx(v) { Alpine.store('regUI').slide = v },
                prev() { if (this.slides.length) { this.idx = (this.idx - 1 + this.slides.length) % this.slides.length } },
                next() { if (this.slides.length) { this.idx = (this.idx + 1) % this.slides.length } },
                startX: 0,
                onDown(e) { this.startX = e.touches ? e.touches[0].clientX : e.clientX },
                onUp(e) {
                    const endX = e.changedTouches ? e.changedTouches[0].clientX : e.clientX;
                    const dx = endX - this.startX;
                    if (Math.abs(dx) > 40) { dx < 0 ? this.next() : this.prev() }
                },
                zoom: false
            }" x-on:keydown.arrow-left.window="prev()"
                x-on:keydown.arrow-right.window="next()">

                {{-- Vitrine principal --}}
                <div class="relative aspect-video w-full rounded-lg bg-black overflow-hidden"
                    x-on:mousedown="onDown($event)" x-on:mouseup="onUp($event)" x-on:touchstart.passive="onDown($event)"
                    x-on:touchend.passive="onUp($event)">

                    <img :src="slides[idx]?.url || ''" :alt="slides[idx]?.label || ''"
                        class="absolute inset-0 h-full w-full object-contain select-none cursor-zoom-in"
                        x-transition.opacity draggable="false" @click="zoom = true">

                    {{-- Indicador --}}
                    <div
                        class="absolute left-2 top-2 rounded-full bg-black/70 px-2.5 py-0.5 text-xs font-medium text-white">
                        <span x-text="slides[idx]?.label || '—'"></span>
                        <span class="opacity-75">—</span>
                        <span x-text="(idx + 1) + ' / ' + slides.length"></span>
                    </div>

                    {{-- Setas --}}
                    <button type="button" @click.stop="prev()"
                        class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/85 px-3 py-1.5 text-lg hover:bg-white"
                        aria-label="Anterior">‹</button>
                    <button type="button" @click.stop="next()"
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/85 px-3 py-1.5 text-lg hover:bg-white"
                        aria-label="Próxima">›</button>
                </div>

                {{-- Thumbs --}}
                <div class="mt-3 flex gap-2 overflow-x-auto sm:overflow-visible sm:grid sm:grid-cols-5">
                    <template x-for="(s, i) in slides" :key="'thumb-' + i">
                        <button type="button"
                            class="relative shrink-0 w-[24vw] max-w-[110px] sm:w-auto aspect-square rounded overflow-hidden ring-2 transition"
                            :class="i === idx ? 'ring-indigo-500' : 'ring-transparent hover:ring-gray-300'"
                            @click="idx = i">
                            <img :src="s.url" :alt="s.label"
                                class="absolute inset-0 w-full h-full object-cover select-none" draggable="false">
                        </button>
                    </template>
                </div>

                {{-- CAMADA DE ZOOM --}}
                <div x-show="zoom" x-transition.opacity
                    class="fixed inset-0 z-[200] bg-black/90 flex items-center justify-center p-4"
                    @click.self="zoom = false">
                    <img :src="slides[idx]?.url || ''" :alt="slides[idx]?.label || ''"
                        class="max-h-[90vh] max-w-[95vw] object-contain select-none">
                    <button @click="zoom = false"
                        class="absolute top-4 right-4 rounded-full bg-white/80 px-3 py-1 text-lg">✕</button>
                </div>
            </div>

            {{-- ===== /CARROSSEL ===== --}}

            {{-- ===== INFORMAÇÕES (mobile first) ===== --}}
            <div class="space-y-4">
                <div>
                    <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Placa</div>
                    <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                        x-text="Alpine.store('regUI').data.placa"></div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Marca</div>
                        <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                            x-text="Alpine.store('regUI').data.marca ?? '—'"></div>
                    </div>
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Modelo</div>
                        <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                            x-text="Alpine.store('regUI').data.modelo"></div>
                    </div>
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</div>
                        <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                            x-text="Alpine.store('regUI').data.tipo === 'carro' ? 'Carro' : 'Moto'"></div>
                    </div>
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Status</div>
                        <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                            x-text="Alpine.store('regUI').data.no_patio ? 'No pátio' : 'Saiu'"></div>
                    </div>
                </div>

                <template x-if="Alpine.store('regUI').data.itens.length">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Itens do veículo
                        </div>
                        <div class="mt-1 flex flex-wrap gap-2">
                            <template x-for="(nome, idx) in Alpine.store('regUI').data.itens" :key="idx">
                                <span
                                    class="rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-[11px] sm:text-xs text-gray-700 dark:text-gray-200"
                                    x-text="nome"></span>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="Alpine.store('regUI').data.observacao">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Observação</div>
                        <div class="whitespace-pre-line text-sm sm:text-base text-gray-900 dark:text-gray-100"
                            x-text="Alpine.store('regUI').data.observacao"></div>
                    </div>
                </template>

                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Condutor (reboque)
                        </div>
                        <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                            x-text="Alpine.store('regUI').data.reboque_condutor"></div>
                    </div>
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Placa (reboque)
                        </div>
                        <div class="text-sm sm:text-base text-gray-900 dark:text-gray-100"
                            x-text="Alpine.store('regUI').data.reboque_placa"></div>
                    </div>
                </div>

                <template x-if="Alpine.store('regUI').data.assinatura">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Assinatura</div>
                        <img :src="Alpine.store('regUI').data.assinatura"
                            class="mt-1 h-24 sm:h-28 w-auto rounded border object-contain dark:border-gray-700"
                            alt="">
                    </div>
                </template>
            </div>
            {{-- ===== /INFORMAÇÕES ===== --}}
        </div>
    </x-modal>



</x-app-layout>
