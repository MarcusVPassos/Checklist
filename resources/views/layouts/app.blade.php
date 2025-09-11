<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{
    theme: (localStorage.theme ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')),
    toggle() {
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
        localStorage.theme = this.theme;
        document.documentElement.classList.toggle('dark', this.theme === 'dark');
    }
}" x-init="document.documentElement.classList.toggle('dark', theme === 'dark')"
    :class="theme === 'dark' ? 'dark' : ''">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script>
        (() => {
            const t = localStorage.theme;
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <script src="https://unpkg.com/signature_pad@4.0.10/dist/signature_pad.umd.min.js"></script>
    <script>
        // função global que inicializa a assinatura dentro de um container (página ou modal)
        window.initSignaturePad = function(root = document) {
            const box = document.querySelector('.e-signpad');
            if (!box) return;
            const form = box.closest('form');
            const canvas = box.querySelector('#sig-canvas');
            const clear = box.querySelector('#sig-clear');
            const hidden = box.querySelector('#sig-b64');
            if (!canvas || typeof SignaturePad === 'undefined') return;

            const pad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255,255,255,1)'
            });
            const fit = () => {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = canvas.getBoundingClientRect();
                const data = pad.toData();
                canvas.style.width = '100%';
                canvas.style.height = '200px';
                canvas.width = Math.round(rect.width * ratio);
                canvas.height = Math.round(200 * ratio);
                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);
                pad.clear();
                if (data.length) pad.fromData(data);
            };
            fit();
            addEventListener('resize', () => requestAnimationFrame(fit));

            clear?.addEventListener('click', () => {
                pad.clear();
                if (hidden) hidden.value = '';
            });
            pad.addEventListener('endStroke', () => {
                if (hidden && !pad.isEmpty()) hidden.value = pad.toDataURL('image/png');
            });

            // se for "obrigatório" (create), bloqueia submit sem assinar
            const obrigatorio = box.dataset.required === '1';
            form?.addEventListener('submit', (e) => {
                if (obrigatorio && pad.isEmpty() && !(hidden && hidden.value)) {
                    e.preventDefault();
                    alert('Assine para continuar.');
                    return;
                }
                if (!pad.isEmpty() && hidden) hidden.value = pad.toDataURL('image/png');
            });
        };

        // página normal (create/edit completos) — inicializa na carga
        document.addEventListener('DOMContentLoaded', () => window.initSignaturePad(document));
    </script>

    {{-- Pré-conexão de fontes e Vite para assets (CSS/JS) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        {{-- Partial da navigation --}}
        @include('layouts.navigation')

        {{-- SLOT nomeado $header (igual em <x-app-layout>) --}}
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        {{-- SLOT principal do layout de app --}}
        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>

</html>
