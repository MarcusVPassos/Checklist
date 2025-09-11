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
<div class="mt-8 e-signpad">
    <x-input-label value="Assinatura {{ $isEdit ? '(opcional para substituir)' : '(obrigatória)' }}" />

    {{-- Base64 via canvas (preferido) --}}
    <div class="rounded-md border bg-white p-2 mt-2">
        <canvas id="sig-canvas" style="width:100%; height:200px;"></canvas>
    </div>
    <div class="flex items-center gap-2 mt-2">
        <button type="button" id="sig-clear" class="rounded-md border px-3 py-2 text-sm">Limpar</button>
        <span class="text-xs text-gray-500">Assine no quadro
            {{ $isEdit ? 'se quiser substituir' : 'para continuar' }}.</span>
    </div>
    <input type="hidden" name="assinatura_b64" id="sig-b64" value="{{ old('assinatura_b64') }}">
    <x-input-error :messages="$errors->get('assinatura_b64')" class="mt-2" />

    {{-- Fallback por arquivo --}}
    <div class="mt-3">
        <x-input-label value="Ou envie um arquivo de imagem (opcional)" />
        <input type="file" name="assinatura" accept="image/*" class="mt-1">
        <x-input-error :messages="$errors->get('assinatura')" class="mt-2" />
    </div>

    @if ($isEdit && $registro->assinatura_path)
        <div class="mt-3">
            <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">Assinatura atual</div>
            <img class="h-24 rounded border" src="{{ asset('storage/' . $registro->assinatura_path) }}"
                alt="Assinatura atual">
        </div>
    @endif
</div>

{{-- ========== FOTOS ========== --}}
<div class="mt-10 space-y-6" x-cloak>
    {{-- CREATE: inputs por posição (obrigatórios variam por tipo) --}}
    @unless ($isEdit)
        <template x-if="tipo === 'carro'">
            <section x-transition>
                <div class="mb-1 flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-medium">Fotos — Carro</h3>
                    <p class="text-xs sm:text-sm text-gray-600">Campos com <span class="text-red-600 font-semibold">*</span>
                        são obrigatórios.</p>
                </div>
                <div class="divide-y rounded-md border">
                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
                        @foreach ($carroObrig as $name => $label)
                            <div>
                                <x-input-label :for="$name" :value="$label" />
                                <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*"
                                    required class="mt-1 block w-full">
                                <x-input-error :messages="$errors->get($name)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
                        @foreach ($carroOpc as $name => $label)
                            <div>
                                <x-input-label :for="$name" :value="$label" />
                                <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*"
                                    class="mt-1 block w-full">
                                <x-input-error :messages="$errors->get($name)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </template>

        <template x-if="tipo === 'moto'">
            <section x-transition>
                <div class="mb-1 flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-medium">Fotos — Moto</h3>
                    <p class="text-xs sm:text-sm text-gray-600">Campos com <span class="text-red-600 font-semibold">*</span>
                        são obrigatórios.</p>
                </div>
                <div class="divide-y rounded-md border">
                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
                        @foreach ($motoObrig as $name => $label)
                            <div>
                                <x-input-label :for="$name" :value="$label" />
                                <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*"
                                    required class="mt-1 block w-full">
                                <x-input-error :messages="$errors->get($name)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
                        @foreach ($motoOpc as $name => $label)
                            <div>
                                <x-input-label :for="$name" :value="$label" />
                                <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*"
                                    class="mt-1 block w-full">
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
        <div>
            <x-input-label value="Imagens atuais" />
            @if ($registro->imagens->isEmpty())
                <p class="text-sm text-gray-500 mt-2">Nenhuma imagem enviada.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mt-3">
                    @foreach ($registro->imagens as $img)
                        <div class="rounded border p-2">
                            <img class="w-full h-32 object-cover rounded" src="{{ asset('storage/' . $img->path) }}"
                                alt="{{ $img->posicao }}">
                            <label class="mt-2 inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" name="remove_imagens[]" value="{{ $img->id }}">
                                <span>Remover</span>
                            </label>
                            <div class="text-xs text-gray-500 mt-1">{{ $img->posicao ?? '—' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- troca por posição (opcional) --}}
        <div class="mt-6">
            <x-input-label value="Substituir fotos por posição (opcional)" />
            <p class="text-xs text-gray-500 mb-2">Se enviar uma foto para uma posição, ela substitui a anterior.</p>

            <template x-if="tipo === 'carro'">
                <div class="grid grid-cols-1 gap-4 p-0 sm:grid-cols-2">
                    @foreach (array_merge($carroObrig, $carroOpc) as $name => $label)
                        <div>
                            <x-input-label :for="'r_' . $name" :value="$label" />
                            <input type="file" name="{{ $name }}" id="r_{{ $name }}"
                                accept="image/*" class="mt-1 block w-full">
                            <x-input-error :messages="$errors->get($name)" class="mt-2" />
                        </div>
                    @endforeach
                </div>
            </template>

            <template x-if="tipo === 'moto'">
                <div class="grid grid-cols-1 gap-4 p-0 sm:grid-cols-2">
                    @foreach (array_merge($motoObrig, $motoOpc) as $name => $label)
                        <div>
                            <x-input-label :for="'r_' . $name" :value="$label" />
                            <input type="file" name="{{ $name }}" id="r_{{ $name }}"
                                accept="image/*" class="mt-1 block w-full">
                            <x-input-error :messages="$errors->get($name)" class="mt-2" />
                        </div>
                    @endforeach
                </div>
            </template>
        </div>

        {{-- extras (sem posição) --}}
        <div class="mt-6">
            <x-input-label value="Adicionar outras imagens (opcional)" />
            <input type="file" name="imagens[]" multiple accept="image/*" class="mt-2">
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
    </style>
@endpush

@push('scripts')
    {{-- Carregue essa lib uma única vez no layout ou aqui (precisa estar presente no modal também) --}}
    <script src="https://unpkg.com/signature_pad@4.0.10/dist/signature_pad.umd.min.js"></script>
    <script>
        // Inicializa a assinatura dentro de um "root" (document ou um container do modal)
        window.initSignaturePad = function initSignaturePad(root = document) {
            const box = root.querySelector('.e-signpad');
            if (!box) return;

            const form = box.closest('form');
            const canvas = box.querySelector('#sig-canvas');
            const clear = box.querySelector('#sig-clear');
            const hidden = box.querySelector('#sig-b64');

            // evita inicializar duas vezes no mesmo nó
            if (!canvas || canvas.__pad) return;

            const pad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255,255,255,1)'
            });
            canvas.__pad = pad; // marca

            // 200px de altura visual
            canvas.style.height = '200px';

            const fit = () => {
                // Só ajusta se o elemento estiver visível (no modal aberto ele estará)
                const rect = canvas.getBoundingClientRect();
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const data = pad.toData();

                // width/height CSS
                canvas.style.width = rect.width + 'px';
                canvas.style.height = '200px';

                // width/height internos (alta definição)
                canvas.width = Math.round(rect.width * ratio);
                canvas.height = Math.round(200 * ratio);

                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);

                pad.clear();
                if (data.length) pad.fromData(data);
            };

            // Ajusta após o layout “assentar”
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

        // Para páginas de CREATE (carregadas inteiras)
        document.addEventListener('DOMContentLoaded', () => window.initSignaturePad(document));
    </script>
@endpush
