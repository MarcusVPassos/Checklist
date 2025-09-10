<x-app-layout>
    {{-- named slot "header" do componente Blade <x-app-layout> --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Novo Registro
        </h2>
    </x-slot>

    {{-- Alpine: estado local do formulário (tipo e helper p/ uppercase da placa) --}}
    <div class="py-6" x-data="{
        tipo: @js(old('tipo', 'carro')),  {{-- @js serializa o PHP->JS; old() repopula em caso de erro --}}
        toUpper(e) { e.target.value = e.target.value.toUpperCase() },
    }">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white dark:bg-gray-800 p-5 sm:p-6 shadow">
                {{-- FORM (com CSRF e multipart p/ upload) --}}
                <form method="POST" action="{{ route('registros.store') }}" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf {{-- token anti-CSRF obrigatório em POST --}}

                    {{-- Linha 1: Placa, Tipo, Marca --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <x-input-label for="placa" value="Placa" />
                            {{-- old('placa') mantém valor quando validação falha; x-on:input uppercase ao digitar --}}
                            <x-text-input id="placa" name="placa" type="text"
                                class="mt-1 block w-full uppercase tracking-wider" maxlength="8"
                                value="{{ old('placa') }}" required x-on:input="toUpper($event)"
                                placeholder="ABC1D23" />
                            <x-input-error :messages="$errors->get('placa')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tipo" value="Tipo" />
                            {{-- x-model mantém o reativo do Alpine para alternar bloco Carro/Moto --}}
                            <select id="tipo" name="tipo"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900" x-model="tipo"
                                required>
                                <option value="carro">Carro</option>
                                <option value="moto">Moto</option>
                            </select>
                            <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="marca_id" value="Marca" />
                            {{-- popular via controller (compact('marcas')); old seleciona a anterior --}}
                            <select id="marca_id" name="marca_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900" required>
                                <option value="" disabled {{ old('marca_id') ? '' : 'selected' }}>Selecione…
                                </option>
                                @foreach ($marcas as $marca)
                                    <option value="{{ $marca->id }}" @selected(old('marca_id') == $marca->id)>{{ $marca->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('marca_id')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Linha 2: Modelo, No pátio --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <x-input-label for="modelo" value="Modelo" />
                            <x-text-input id="modelo" name="modelo" type="text" class="mt-1 block w-full"
                                value="{{ old('modelo') }}" required />
                            <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
                        </div>

                        <div class="flex items-end">
                            {{-- hidden + checkbox: garante envio 0/1 para booleano --}}
                            <input type="hidden" name="no_patio" value="0">
                            <label class="inline-flex items-center gap-2">
                                <input id="no_patio" type="checkbox" name="no_patio" value="1"
                                    @checked(old('no_patio', 1)) class="rounded border-gray-300">
                                <span>No pátio</span>
                            </label>
                            <x-input-error :messages="$errors->get('no_patio')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Observação --}}
                    <div>
                        <x-input-label for="observacao" value="Observação" />
                        <textarea id="observacao" name="observacao" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900"
                            rows="3">{{ old('observacao') }}</textarea>
                        <x-input-error :messages="$errors->get('observacao')" class="mt-2" />
                    </div>

                    {{-- Reboque (obrigatório) --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="reboque_condutor" value="Condutor do Reboque" />
                            <x-text-input id="reboque_condutor" name="reboque_condutor" type="text"
                                class="mt-1 block w-full" value="{{ old('reboque_condutor') }}" required />
                            <x-input-error :messages="$errors->get('reboque_condutor')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="reboque_placa" value="Placa do Reboque" />
                            <x-text-input id="reboque_placa" name="reboque_placa" type="text"
                                class="mt-1 block w-full uppercase" value="{{ old('reboque_placa') }}"
                                x-on:input="toUpper($event)" required />
                            <x-input-error :messages="$errors->get('reboque_placa')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Itens (N:N) --}}
                    <div>
                        <x-input-label value="Itens" />
                        <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($itens as $item)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="itens[]" value="{{ $item->id }}"
                                        @checked(collect(old('itens', []))->contains($item->id)) class="rounded border-gray-300">
                                    <span>{{ $item->nome }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('itens')" class="mt-2" />
                        <x-input-error :messages="$errors->get('itens.*')" class="mt-2" />
                    </div>

                    {{-- Assinatura (via SignaturePad) --}}
                    <div class="space-y-2 e-signpad">
                        <x-input-label value="Assinatura (obrigatória)" />
                        <div class="rounded-md border bg-white p-2">
                            <canvas id="sig-canvas" style="width: 100%; height: 200px;"></canvas>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" id="sig-clear" class="rounded-md border px-3 py-2 text-sm">
                                Limpar
                            </button>
                            <span class="text-xs text-gray-500">Assine no quadro para continuar.</span>
                        </div>

                        <input type="hidden" name="assinatura_b64" id="sig-b64"
                            value="{{ old('assinatura_b64') }}">
                    </div>


                    {{-- ================= FOTOS ================= --}}
                    {{-- Os arrays com labels por tipo ficam no escopo da view --}}
                    <div class="space-y-6">
                        @php
                            $carroObrig = [
                                'frente' => 'Frente *',
                                'lado_direito' => 'Lado direito *',
                                'lado_esquerdo' => 'Lado esquerdo *',
                                'traseira' => 'Traseira *',
                                'capo_aberto' => 'Capô aberto *',
                                'numero_do_motor' => 'Número do motor *',
                                'painel_lado_direito' => 'Painel Lado Direito *',
                                'painel_lado_esquerdo' => 'Painel Lado Esquerdo *',
                            ];
                            $carroOpc = [
                                'bateria_carro' => 'Bateria (carro)',
                                'chave_carro' => 'Chave (carro)',
                                'estepe_do_veiculo' => 'Estepe do veículo',
                            ];
                            $motoObrig = [
                                'frente' => 'Frente *',
                                'lado_direito' => 'Lado direito *',
                                'lado_esquerdo' => 'Lado esquerdo *',
                                'traseira' => 'Traseira *',
                                'motor_lado_direito' => 'Motor Lado Direito *',
                                'motor_lado_esquerdo' => 'Motor Lado Esquerdo *',
                                'painel_moto' => 'Painel (moto) *',
                            ];
                            $motoOpc = [
                                'chave_moto' => 'Chave (moto)',
                                'bateria_moto' => 'Bateria (moto)',
                            ];
                        @endphp

                        {{-- Bloco Carro (Alpine x-if) --}}
                        <template x-if="tipo === 'carro'">
                            <section x-transition>
                                <!-- TODO: aqui fica exatamente o que você já tinha do carro -->
                                <div class="mb-1 flex items-center justify-between">
                                    <h3 class="text-base sm:text-lg font-medium">Fotos — Carro</h3>
                                    <p class="text-xs sm:text-sm text-gray-600">
                                        Campos com <span class="text-red-600 font-semibold">*</span> são obrigatórios.
                                    </p>
                                </div>

                                <div class="divide-y rounded-md border">
                                    <summary
                                        class="cursor-pointer select-none px-4 py-3 text-sm font-medium bg-gray-50 group-open:rounded-t-md">
                                        Obrigatórias
                                    </summary>
                                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
                                        @foreach ($carroObrig as $name => $label)
                                            <x-photo-input :name="$name" :label="$label" :required="true" />
                                        @endforeach
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
                                        @foreach ($carroOpc as $name => $label)
                                            <x-photo-input :name="$name" :label="$label" />
                                        @endforeach
                                    </div>
                                </div>
                            </section>
                        </template>

                        {{-- Bloco Moto (Alpine x-if) --}}
                        <template x-if="tipo === 'moto'">
                            <section x-transition>
                                <!-- TODO: aqui fica exatamente o que você já tinha da moto -->
                                <div class="mb-1 flex items-center justify-between">
                                    <h3 class="text-base sm:text-lg font-medium">Fotos — Moto</h3>
                                    <p class="text-xs sm:text-sm text-gray-600">
                                        Campos com <span class="text-red-600 font-semibold">*</span> são obrigatórios.
                                    </p>
                                </div>

                                <div class="divide-y rounded-md border">
                                    <summary
                                        class="cursor-pointer select-none px-4 py-3 text-sm font-medium bg-gray-50 group-open:rounded-t-md">
                                        Obrigatórias
                                    </summary>
                                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
                                        @foreach ($motoObrig as $name => $label)
                                            <x-photo-input :name="$name" :label="$label" :required="true" />
                                        @endforeach
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
                                        @foreach ($motoOpc as $name => $label)
                                            <x-photo-input :name="$name" :label="$label" />
                                        @endforeach
                                    </div>
                                </div>
                            </section>
                        </template>

                    </div>
                    {{-- =============== /FOTOS =============== --}}

                    {{-- Ações --}}
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end pt-4 border-t">
                        <a href="{{ route('registros.index') }}"
                            class="inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </a>
                        <x-primary-button type="submit" class="justify-center">
                            Criar registro
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- estilo específico: melhorar experiência do canvas no iOS --}}
    @push('styles')
    <style>
        /* impede a página de “rolar” enquanto assina no iOS */
        .e-signpad canvas {
            touch-action: none;
        }
    </style>
    @endpush

    @push('scripts')
    {{-- SignaturePad + boot da captura de assinatura --}}
    <script src="https://unpkg.com/signature_pad@4.0.10/dist/signature_pad.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form[action="{{ route('registros.store') }}"]');
            const box = document.querySelector('.e-signpad');
            const canvas = box.querySelector('#sig-canvas');
            const clear = box.querySelector('#sig-clear');
            const hidden = box.querySelector('#sig-b64');

            const pad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255,255,255,1)'
            });

            // redimensiona SEM perder o traço (salva/restaura o path)  || (fit) ajusta o canvas ao DPR sem perder o traço
            const fit = () => {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = canvas.getBoundingClientRect();

                // defina TAMBÉM o tamanho CSS (importante no iOS)
                canvas.style.width = rect.width + 'px';
                canvas.style.height = rect.height + 'px';

                // salvar o desenho antes de mexer no tamanho interno
                const data = pad.toData();

                canvas.width = Math.round(rect.width * ratio);
                canvas.height = Math.round(rect.height * ratio);
                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);

                // restaura o traço
                pad.clear();
                if (data.length) pad.fromData(data);
            };

            // 200px de altura visual (a sua div já dá largura 100%)
            canvas.style.height = '200px';
            // monta uma vez
            fit();

            // iOS muda viewport em rotação/teclado → debounce do resize
            let rAF;
            const onResize = () => {
                cancelAnimationFrame(rAF);
                rAF = requestAnimationFrame(fit);
            };
            window.addEventListener('resize', onResize, {
                passive: true
            });
            window.addEventListener('orientationchange', onResize);

            // limpar
            clear.addEventListener('click', () => {
                pad.clear();
                hidden.value = '';
            });

            // dica: já captura a cada traço (se houver resize depois, você não perde)
            pad.addEventListener('endStroke', () => {
                if (!pad.isEmpty()) hidden.value = pad.toDataURL('image/png');
            });

            // no submit, garante o base64
            form.addEventListener('submit', (e) => {
                if (pad.isEmpty() && !hidden.value) {
                    e.preventDefault();
                    alert('Assine para continuar.');
                    return;
                }
                if (!hidden.value) hidden.value = pad.toDataURL('image/png');
            });
        });
    </script>
    @endpush

    @push('scripts')
    {{-- Helpers Alpine: previsualização de arquivo (se usar input file convencional) --}}
    <script>
        function filePreview() {
            return {
                url: null,
                init() {
                    this.url = null;
                },
                pick(e) {
                    const f = e.target.files?.[0];
                    if (!f) {
                        this.url = null;
                        return;
                    }
                    this.url = URL.createObjectURL(f);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
