<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
        theme: (localStorage.theme ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')),
        toggle() {
          this.theme = this.theme === 'dark' ? 'light' : 'dark';
          localStorage.theme = this.theme;
          document.documentElement.classList.toggle('dark', this.theme === 'dark');
        }
      }"
      x-init="document.documentElement.classList.toggle('dark', theme === 'dark')"
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