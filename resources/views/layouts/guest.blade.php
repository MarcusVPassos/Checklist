<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 dark:text-gray-100">
    <div class="relative flex flex-col items-center min-h-screen pt-6 justify-center sm:pt-0">

        <!-- camada da imagem de fundo -->
        <div class="absolute inset-0 bg-[url('/assets/LojaBrascar.jpeg')] bg-cover bg-center blur-sm bg-black/50"></div>

        <!-- overlay preta -->
        <div class="absolute inset-0 bg-black/60"></div>

        <!-- conteúdo -->
        <div
            class="relative z-10 w-full p-6 mt-6 overflow-hidden bg-white shadow-md mx-6 sm:max-w-md dark:bg-gray-800 sm:rounded-lg">
            <div class="flex justify-center mb-12">
                <a href="/">
                    <x-application-logo class="w-20 h-20 text-gray-500 fill-current" />
                </a>
            </div>
            {{ $slot }}
        </div>
    </div>
</body>

</html>
