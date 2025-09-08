<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Novo Registro
        </h2>
    </x-slot>

    <div class="py-6" x-data="{
        tipo: @js(old('tipo', 'carro')),
        toUpper(e) { e.target.value = e.target.value.toUpperCase() },
    }">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white dark:bg-gray-800 p-5 sm:p-6 shadow">
                <form method="POST" action="{{ route('registros.store') }}" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf

                    {{-- Linha 1: Placa, Tipo, Marca --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <x-input-label for="placa" value="Placa" />
                            <x-text-input id="placa" name="placa" type="text"
                                class="mt-1 block w-full uppercase tracking-wider" maxlength="8"
                                value="{{ old('placa') }}" required x-on:input="toUpper($event)"
                                placeholder="ABC1D23" />
                            <x-input-error :messages="$errors->get('placa')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tipo" value="Tipo" />
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

                    {{-- Assinatura (obrigatória) --}}
                    {{-- <div x-data="filePreview()" x-init="init()">
                        <x-input-label for="assinatura" value="Assinatura (obrigatória)" />
                        <input id="assinatura" name="assinatura" type="file"
                               class="mt-1 block w-full text-sm text-gray-900
                                      file:mr-4 file:rounded-md file:border-0 file:bg-indigo-600
                                      file:px-4 file:py-2 file:text-white hover:file:bg-indigo-700"
                               accept=".png,.jpg,.jpeg,.webp" required
                               @change="pick($event)">
                        <template x-if="url">
                            <div class="mt-2">
                                <img :src="url" alt="Prévia assinatura" class="h-24 rounded border object-contain">
                            </div>
                        </template>
                        <x-input-error :messages="$errors->get('assinatura')" class="mt-2" />
                    </div> --}}
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

                        {{-- Carro --}}
                        <!-- Carro -->
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

                        <!-- Moto -->
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

    {{-- Helpers Alpine --}}
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

    {{-- Lib do pacote (public/vendor/sign-pad/sign-pad.min.js) --}}
    <script src="https://unpkg.com/signature_pad@4.0.10/dist/signature_pad.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form[action="{{ route('registros.store') }}"]');
            const box = document.querySelector('.e-signpad');
            const canvas = box.querySelector('#sig-canvas');
            const clear = box.querySelector('#sig-clear');
            const hidden = box.querySelector('#sig-b64');

            // cria o pad
            const pad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255,255,255,1)'
            });

            // deixa o canvas nítido e sem deslocamento
            const fit = () => {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = canvas.getBoundingClientRect();
                canvas.width = Math.round(rect.width * ratio);
                canvas.height = Math.round(rect.height * ratio);
                canvas.getContext('2d').setTransform(ratio, 0, 0, ratio, 0, 0);

                // se já havia base64 (volta de validação), redesenha
                if (hidden.value) pad.fromDataURL(hidden.value).catch(() => {});
            };
            fit();
            window.addEventListener('resize', fit, {
                passive: true
            });

            // limpar
            clear.addEventListener('click', () => {
                pad.clear();
                hidden.value = '';
            });

            // no submit do form, garante a base64 (e bloqueia se estiver vazio)
            form.addEventListener('submit', (e) => {
                if (pad.isEmpty() && !hidden.value) {
                    e.preventDefault();
                    alert('Assine para continuar.');
                    return;
                }
                hidden.value = pad.toDataURL('image/png');
            });
        });
    </script>



    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.e-signpad canvas').forEach((canvas) => {

                const fit = () => {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    // mede o tamanho real que o canvas ocupa na tela
                    const rect = canvas.getBoundingClientRect();
                    // ajusta o "tamanho interno" (em pixels) para bater com o visual
                    canvas.width = Math.round(rect.width * ratio);
                    canvas.height = Math.round(rect.height * ratio);
                    // aplica a matriz para que 1 unidade de desenho = 1px CSS
                    const ctx = canvas.getContext('2d');
                    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                };

                fit(); // na carga
                window.addEventListener('resize', fit, {
                    passive: true
                }); // em resize
            });
        });
    </script>
</x-app-layout>
