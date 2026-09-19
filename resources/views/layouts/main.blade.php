<!DOCTYPE html>
<html lang="{{ App::getLocale() }}" class="site-public-root" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @stack('meta')
    <title>@yield('title', 'Direction de la Pension Civile')</title>
    <x-fonts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <link href="{{ asset('build/assets/app-CFGfTGFn.css') }}" rel="stylesheet"> --}}
    @stack('styles')
</head>

<body class="site-public w-full min-h-dvh flex flex-col bg-[#e6ebf1] text-gray-800 overflow-x-hidden font-sans antialiased">
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

    <x-header />
    <main class="container mx-auto flex-1 w-full min-h-[calc(100dvh-13rem)] bg-white">
        @yield('content')
    </main>
    <x-footer />
    <x-contact-info-bar borderType="top"  />
    @stack('scripts')
    {{-- <script src="{{ asset('build/assets/app-CbEvcXly.js') }}"></script> --}}
</body>
</html>
