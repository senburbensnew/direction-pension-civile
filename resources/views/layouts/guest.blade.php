<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link href="{{ asset('build/assets/app-CFGfTGFn.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">

    <div class="min-h-screen flex">

        {{-- Left branding panel --}}
        <div class="hidden lg:flex lg:w-1/2 relative flex-col items-center justify-between py-16 px-12 overflow-hidden"
             style="background: linear-gradient(145deg, #1e3a8a 0%, #1d4ed8 60%, #3b82f6 100%);">


            {{-- Background photo overlay --}}
            <div class="absolute inset-0">
                <img src="{{ asset('images/directrice-landscape.jpg') }}"
                     class="w-full h-full object-cover opacity-30"
                     alt="">
            </div>
            {{-- Decorative circle --}}
            <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full opacity-10"
                 style="background: radial-gradient(circle, #93c5fd, transparent)"></div>
            <div class="absolute -top-16 -right-16 w-72 h-72 rounded-full opacity-10"
                 style="background: radial-gradient(circle, #bfdbfe, transparent)"></div>

            {{-- Top logo --}}
            <div class="relative z-10 flex items-center gap-3">
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                     class="w-12 h-12 object-contain"
                     alt="Logo DPC">
                <span class="text-white/80 text-sm font-medium tracking-wide uppercase">République d'Haïti</span>
            </div>

            {{-- Centre content --}}
            <div class="relative z-10 text-center text-white">
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                     class="w-28 h-28 object-contain mx-auto mb-8 drop-shadow-lg"
                     alt="Logo DPC">
                <h1 class="text-3xl font-bold mb-4 leading-tight">
                    Direction de la<br>Pension Civile
                </h1>
                <p class="text-blue-100 text-base leading-relaxed max-w-xs mx-auto">
                    Votre partenaire de confiance pour une retraite sereine et sécurisée.
                </p>
            </div>

            {{-- Bottom tagline --}}
            <p class="relative z-10 text-blue-200/60 text-xs text-center">
                Ministère de l'Économie et des Finances
            </p>
        </div>

        {{-- Right form panel --}}
        <div class="flex-1 flex flex-col items-center justify-center min-h-screen bg-gray-50 px-6 py-12 overflow-y-auto">

            <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-10">
                {{-- Logo shown inside card on all screen sizes --}}
                <div class="flex flex-col items-center mb-8">
                    <a href="{{ url('/') }}" title="Retour à l'accueil">
                        <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                             class="w-20 h-20 object-contain mb-3 hover:opacity-80 transition-opacity"
                             alt="Logo DPC">
                    </a>
                    <p class="text-gray-500 text-xs font-medium tracking-wide uppercase text-center">
                        Direction de la Pension Civile
                    </p>
                </div>
                {{ $slot }}
            </div>

            <p class="mt-8 text-xs text-gray-400 text-center">
                &copy; {{ date('Y') }} Direction de la Pension Civile — République d'Haïti
            </p>
        </div>
    </div>

    <script src="{{ asset('build/assets/app-CbEvcXly.js') }}"></script>
</body>
</html>
