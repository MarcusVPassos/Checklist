<x-app-layout>
    {{-- named slot "header" do componente Blade <x-app-layout> --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Novo Registro
        </h2>
    </x-slot>

    {{-- Alpine: estado local do formulário (tipo e helper p/ uppercase da placa) --}}
    <div class="py-6" x-data="{
        tipo: @js(old('tipo', 'carro')),
        {{-- @js serializa o PHP->JS; old() repopula em caso de erro --}}
        toUpper(e) { e.target.value = e.target.value.toUpperCase() },
    }">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white dark:bg-gray-800 p-5 sm:p-6 shadow">
                {{-- FORM (com CSRF e multipart p/ upload) --}}
                <form method="POST" action="{{ route('registros.store') }}" enctype="multipart/form-data"
                      x-data="{ tipo: @js(old('tipo', 'carro')) }" class="space-y-6">
                    @csrf

                    {{-- Partials mantidos --}}
                    @include('registros._form', ['marcas' => $marcas, 'itens' => $itens])
                    @include('registros._media') {{-- aqui ele entende que é CREATE (sem $registro) --}}

                    {{-- Ações --}}
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('registros.index') }}"
                           class="inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm font-medium
                                  text-gray-700 hover:bg-gray-50 border-gray-300
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                  dark:text-gray-200 dark:hover:bg-gray-800 dark:border-gray-700
                                  dark:focus:ring-indigo-400 dark:focus:ring-offset-gray-900">
                            Cancelar
                        </a>
                        <x-primary-button type="submit"
                            class="justify-center
                                   focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                   dark:focus:ring-indigo-400 dark:focus:ring-offset-gray-900">
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
            .e-signpad canvas { touch-action: none; }
            /* garante fundo branco da assinatura (canvas e imagem) em qualquer tema */
            .e-signpad .rounded-md { background: #ffffff; } /* container da assinatura */
            .e-signpad canvas { background: #ffffff !important; }
            .e-signpad img[alt="Assinatura atual"] { background: #ffffff; }
            /* bordas em dark */
            @media (prefers-color-scheme: dark) {
                .e-signpad .rounded-md,
                .e-signpad img { border-color: rgb(55 65 81) !important; } /* gray-700 */
            }
        </style>
    @endpush

    @push('scripts')
        {{-- SignaturePad + boot da captura de assinatura --}}
        <script src="https://unpkg.com/signature_pad@4.0.10/dist/signature_pad.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form  = document.querySelector('form[action="{{ route('registros.store') }}"]');
                const box   = document.querySelector('.e-signpad');
                const canvas= box.querySelector('#sig-canvas');
                const clear = box.querySelector('#sig-clear');
                const hidden= box.querySelector('#sig-b64');

                const pad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255,255,255,1)' // mantém branco no dark
                });

                // redimensiona SEM perder o traço (salva/restaura o path)  || (fit) ajusta o canvas ao DPR sem perder o traço
                const fit = () => {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    const rect  = canvas.getBoundingClientRect();

                    // defina TAMBÉM o tamanho CSS (importante no iOS)
                    canvas.style.width  = rect.width + 'px';
                    canvas.style.height = rect.height + 'px';

                    // salvar o desenho antes de mexer no tamanho interno
                    const data = pad.toData();

                    canvas.width  = Math.round(rect.width * ratio);
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
                window.addEventListener('resize', onResize, { passive: true });
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
                    init() { this.url = null; },
                    pick(e) {
                        const f = e.target.files?.[0];
                        if (!f) { this.url = null; return; }
                        this.url = URL.createObjectURL(f);
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
