@php
    $isEdit = isset($registro);
    $tipoInicial = old('tipo', $registro->tipo ?? 'carro');
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

{{-- ========== ASSINATURA ========== --}}
<div class="mt-8 e-signpad px-4 sm:px-6">
    <x-input-label value="Assinatura {{ $isEdit ? '(opcional para substituir)' : '(obrigatória)' }}"
        class="text-gray-700 dark:text-gray-300" />

    {{-- Base64 via canvas (preferido) --}}
    <div class="rounded-md border bg-white p-2 mt-2 border-gray-300 dark:border-gray-700">
        <canvas id="sig-canvas" style="width:100%; height:200px;"></canvas>
    </div>
    <div class="flex items-center gap-2 mt-2">
        <button type="button" id="sig-clear"
            class="rounded-md border px-3 py-2 text-sm
                   text-gray-700 hover:bg-gray-50 border-gray-300
                   dark:text-gray-200 dark:hover:bg-gray-800 dark:border-gray-700">
            Limpar
        </button>
        <span class="text-xs text-gray-500 dark:text-gray-400">Assine no quadro
            {{ $isEdit ? 'se quiser substituir' : 'para continuar' }}.</span>
    </div>
    <input type="hidden" name="assinatura_b64" id="sig-b64" value="{{ old('assinatura_b64') }}">
    <x-input-error :messages="$errors->get('assinatura_b64')" class="mt-2" />

    {{-- Fallback por arquivo --}}
    <div class="mt-3">
        <x-input-label value="Ou envie um arquivo de imagem (opcional)" class="text-gray-700 dark:text-gray-300" />
        <input type="file" name="assinatura" accept="image/*" class="mt-1 w-full text-gray-900 dark:text-gray-100">
        <x-input-error :messages="$errors->get('assinatura')" class="mt-2" />
    </div>

    @if ($isEdit && $registro->assinatura_path)
        <div class="mt-3">
            <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">Assinatura atual</div>
            <img class="h-20 sm:h-24 rounded border border-gray-300 dark:border-gray-700 bg-white"
                src="{{ asset('storage/' . $registro->assinatura_path) }}" alt="Assinatura atual">
        </div>
    @endif
</div>

{{-- ========== FOTOS ========== --}}
<div class="mt-10 space-y-6" x-cloak>
    {{-- CREATE: inputs por posição (obrigatórios variam por tipo) --}}
    @unless ($isEdit)
        <template x-if="tipo === 'carro'">
            <section x-transition class="px-4 sm:px-6">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">Fotos — Carro</h3>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                        Campos com <span class="text-red-600 font-semibold">*</span> são obrigatórios.
                    </p>
                </div>
                <div class="divide-y rounded-md border border-gray-200 dark:border-gray-700 dark:divide-gray-700">
                    <div class="grid grid-cols-1 gap-3 sm:gap-4 p-4 sm:grid-cols-2">
                        @foreach ($carroObrig as $name => $label)
                            <div>
                                <x-input-label :for="$name" :value="$label"
                                    class="text-gray-700 dark:text-gray-300" />
                                <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*"
                                    required class="mt-1 block w-full text-gray-900 dark:text-gray-100">
                                <x-input-error :messages="$errors->get($name)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:gap-4 p-4 sm:grid-cols-2">
                        @foreach ($carroOpc as $name => $label)
                            <div>
                                <x-input-label :for="$name" :value="$label"
                                    class="text-gray-700 dark:text-gray-300" />
                                <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*"
                                    class="mt-1 block w-full text-gray-900 dark:text-gray-100">
                                <x-input-error :messages="$errors->get($name)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </template>

        <template x-if="tipo === 'moto'">
            <section x-transition class="px-4 sm:px-6">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">Fotos — Moto</h3>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                        Campos com <span class="text-red-600 font-semibold">*</span> são obrigatórios.
                    </p>
                </div>
                <div class="divide-y rounded-md border border-gray-200 dark:border-gray-700 dark:divide-gray-700">
                    <div class="grid grid-cols-1 gap-3 sm:gap-4 p-4 sm:grid-cols-2">
                        @foreach ($motoObrig as $name => $label)
                            <div>
                                <x-input-label :for="$name" :value="$label"
                                    class="text-gray-700 dark:text-gray-300" />
                                <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*"
                                    required class="mt-1 block w-full text-gray-900 dark:text-gray-100">
                                <x-input-error :messages="$errors->get($name)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:gap-4 p-4 sm:grid-cols-2">
                        @foreach ($motoOpc as $name => $label)
                            <div>
                                <x-input-label :for="$name" :value="$label"
                                    class="text-gray-700 dark:text-gray-300" />
                                <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*"
                                    class="mt-1 block w-full text-gray-900 dark:text-gray-100">
                                <x-input-error :messages="$errors->get($name)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </template>
    @endunless

    {{-- EDIT: lista/remoção + troca por posição + extras --}}
    @if ($isEdit)
        {{-- imagens atuais + remover --}}
        <div class="px-4 sm:px-6">
            <x-input-label value="Imagens atuais" class="text-gray-700 dark:text-gray-300" />
            @if ($registro->imagens->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Nenhuma imagem enviada.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 mt-3">
                    @foreach ($registro->imagens as $img)
                        <div class="rounded border p-2 border-gray-200 dark:border-gray-700">
                            <img class="w-full h-24 sm:h-32 object-cover rounded border border-gray-200 dark:border-gray-700"
                                src="{{ asset('storage/' . $img->path) }}" alt="{{ $img->posicao }}">
                            <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="remove_imagens[]" value="{{ $img->id }}"
                                    class="rounded border-gray-300 text-indigo-600
                                              focus:ring-indigo-500 dark:focus:ring-indigo-400
                                              dark:border-gray-700 dark:bg-gray-900">
                                <span>Remover</span>
                            </label>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $img->posicao ?? '—' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- troca por posição (opcional) --}}
        <div class="mt-6 px-4 sm:px-6">
            <x-input-label value="Substituir fotos por posição (opcional)" class="text-gray-700 dark:text-gray-300" />
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                Se enviar uma foto para uma posição, ela substitui a anterior.
            </p>

            <template x-if="tipo === 'carro'">
                <div class="grid grid-cols-1 gap-3 sm:gap-4 p-0 sm:grid-cols-2">
                    @foreach (array_merge($carroObrig, $carroOpc) as $name => $label)
                        <div>
                            <x-input-label :for="'r_' . $name" :value="$label"
                                class="text-gray-700 dark:text-gray-300" />
                            <input type="file" name="{{ $name }}" id="r_{{ $name }}"
                                accept="image/*" class="mt-1 block w-full text-gray-900 dark:text-gray-100">
                            <x-input-error :messages="$errors->get($name)" class="mt-2" />
                        </div>
                    @endforeach
                </div>
            </template>

            <template x-if="tipo === 'moto'">
                <div class="grid grid-cols-1 gap-3 sm:gap-4 p-0 sm:grid-cols-2">
                    @foreach (array_merge($motoObrig, $motoOpc) as $name => $label)
                        <div>
                            <x-input-label :for="'r_' . $name" :value="$label"
                                class="text-gray-700 dark:text-gray-300" />
                            <input type="file" name="{{ $name }}" id="r_{{ $name }}"
                                accept="image/*" class="mt-1 block w-full text-gray-900 dark:text-gray-100">
                            <x-input-error :messages="$errors->get($name)" class="mt-2" />
                        </div>
                    @endforeach
                </div>
            </template>
        </div>

        {{-- extras (sem posição) --}}
        <div class="mt-6 px-4 sm:px-6">
            <x-input-label value="Adicionar outras imagens (opcional)" class="text-gray-700 dark:text-gray-300" />
            <input type="file" name="imagens[]" multiple accept="image/*"
                class="mt-2 w-full text-gray-900 dark:text-gray-100">
            <x-input-error :messages="$errors->get('imagens')" class="mt-2" />
            <x-input-error :messages="$errors->get('imagens.*')" class="mt-2" />
        </div>
    @endif
</div>

@push('styles')
    <style>
        .e-signpad canvas {
            touch-action: none;
        }

        /* Força fundo branco na assinatura (canvas e imagem) em qualquer tema */
        .e-signpad canvas,
        img[alt="Assinatura atual"] {
            background: #ffffff !important;
        }
    </style>
@endpush

@push('scripts')
    {{-- Carregue essa lib uma única vez no layout ou aqui (precisa estar presente no modal também) --}}
    <script src="https://unpkg.com/signature_pad@4.0.10/dist/signature_pad.umd.min.js"></script>
    <script>
        // (sem mudanças de funcionalidade)
        window.initSignaturePad = function initSignaturePad(root = document) {
            const box = root.querySelector('.e-signpad');
            if (!box) return;

            const form = box.closest('form');
            const canvas = box.querySelector('#sig-canvas');
            const clear = box.querySelector('#sig-clear');
            const hidden = box.querySelector('#sig-b64');

            if (!canvas || canvas.__pad) return;

            const pad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255,255,255,1)'
            });
            canvas.__pad = pad;

            canvas.style.height = '200px';

            const fit = () => {
                const rect = canvas.getBoundingClientRect();
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const data = pad.toData();

                canvas.style.width = rect.width + 'px';
                canvas.style.height = '200px';

                canvas.width = Math.round(rect.width * ratio);
                canvas.height = Math.round(200 * ratio);

                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);

                pad.clear();
                if (data.length) pad.fromData(data);
            };

            requestAnimationFrame(fit);
            window.addEventListener('resize', () => requestAnimationFrame(fit), {
                passive: true
            });

            clear?.addEventListener('click', () => {
                pad.clear();
                if (hidden) hidden.value = '';
            });

            pad.addEventListener('endStroke', () => {
                if (hidden && !pad.isEmpty()) hidden.value = pad.toDataURL('image/png');
            });

            form?.addEventListener('submit', (e) => {
                const obrigatorio = {{ isset($registro) ? 'false' : 'true' }};
                if (obrigatorio && pad.isEmpty() && !(hidden && hidden.value)) {
                    e.preventDefault();
                    alert('Assine para continuar.');
                    return;
                }
                if (!pad.isEmpty() && hidden) hidden.value = pad.toDataURL('image/png');
            });
        };

        document.addEventListener('DOMContentLoaded', () => window.initSignaturePad(document));
    </script>
@endpush
