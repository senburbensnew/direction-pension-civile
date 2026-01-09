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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Scripts -->
    <link href="{{ asset('build/assets/app-CFGfTGFn.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="container mx-auto font-sans antialiased border">
    <noscript>
        <div class="fixed inset-0 z-[10001] flex items-center justify-center bg-black bg-opacity-80">
            <div class="bg-white max-w-md w-full mx-4 p-6 rounded-xl shadow-xl text-center">
                <div class="text-red-500 text-5xl mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                    JavaScript désactivé
                </h2>

                <p class="text-gray-600 mb-4">
                    Ce site nécessite JavaScript pour fonctionner correctement.
                    Veuillez activer JavaScript dans votre navigateur et recharger la page.
                </p>

                <p class="text-sm text-gray-500">
                    Sans JavaScript, certaines fonctionnalités ne seront pas disponibles.
                </p>
            </div>
        </div>
    </noscript>

    <div class="min-h-screen bg-gray-100">
        <x-header />
        <main>
            {{ $slot }}
        </main>
        <x-footer />
        <x-contact-info-bar borderType="top" />
    </div>
    <script src="{{ asset('build/assets/app-CbEvcXly.js') }}"></script>
</body>
</html>
