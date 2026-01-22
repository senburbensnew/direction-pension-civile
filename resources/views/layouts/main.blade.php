<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @stack('meta')
    <title>@yield('title', 'Direction de la Pension Civile')</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    <link href="{{ asset('build/assets/app-CFGfTGFn.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body class="container mx-auto bg-gray-100 text-gray-800 border overflow-x-hidden">
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
    <main class="container mx-auto min-h-screen">
        @yield('content')
    </main>
    <x-footer />
    <x-contact-info-bar borderType="top"  />
    <!-- Under Construction Notification -->
{{--     @if(session()->get('site_under_construction', true))
        <div id="construction-alert" 
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-[10000]">
            
            <div class="bg-white rounded-xl shadow-2xl p-8 max-w-lg w-full text-center relative">
                
                <!-- Close Button (×) -->
                <a href="{{ route('toggle.construction') }}" 
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 transition-colors text-xl font-bold">
                    &times;
                </a>

                <!-- Icon -->
                <div class="text-yellow-500 text-5xl mb-4">
                    <i class="fas fa-tools"></i>
                </div>

                <!-- Heading -->
                <h2 class="text-2xl font-bold mb-2 text-gray-800">Site en Construction</h2>

                <!-- Message -->
                <p class="text-gray-600 mb-4">
                    Notre site est actuellement en construction. Merci de votre patience !
                </p>

                <!-- Fermer button -->
                <a href="{{ route('toggle.construction') }}" 
                class="bg-yellow-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-yellow-600 transition-colors">
                    Fermer
                </a>
            </div>
        </div>
    @endif --}}

    @stack('scripts')
    <script src="{{ asset('build/assets/app-CbEvcXly.js') }}"></script>
</body>
</html>
