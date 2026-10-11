<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      @class(['site-public-root', 'guest-auth-page', 'guest-auth-locked' => ! $wide])
      data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <x-fonts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html.guest-auth-page {
            font-size: 100%;
        }

        /* Login / register : viewport fixe, scroll interne si besoin */
        html.guest-auth-locked,
        html.guest-auth-locked body.guest-auth {
            margin: 0;
            height: 100dvh;
            min-height: 100dvh;
            max-height: 100dvh;
            overflow: hidden;
            background: #f9fafb;
        }
        .guest-auth-shell {
            display: flex;
            align-items: stretch;
            justify-content: center;
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100dvh;
            max-height: 100dvh;
            min-height: 0;
            overflow: hidden;
        }
        .guest-auth-form {
            flex: 1 1 0;
            min-width: 0;
            min-height: 0;
            max-height: 100%;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f9fafb;
        }
        .guest-auth-form::before,
        .guest-auth-form::after {
            content: "";
            flex: 1 1 0;
            min-height: 0;
        }
        .guest-auth-form-inner {
            box-sizing: border-box;
            flex: 0 0 auto;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 1.5rem 1.5rem 0.75rem;
        }
        .guest-auth-copy {
            margin: 0;
            padding: 0.75rem 0 0.15rem;
            flex-shrink: 0;
        }
        .guest-auth-form--no-scroll {
            overflow-y: hidden;
            justify-content: center;
        }
        .guest-auth-form--no-scroll::before,
        .guest-auth-form--no-scroll::after {
            display: none;
        }
        .guest-auth-form--no-scroll .guest-auth-form-inner {
            padding-top: 0.75rem;
            padding-bottom: 0.25rem;
        }
        .guest-auth-form--no-scroll .guest-auth-copy {
            padding-top: 0.5rem;
        }

        /* Demande de compte : page fluide, pas de hauteur fixe */
        html.guest-auth-page:not(.guest-auth-locked) body.guest-auth {
            margin: 0;
            min-height: 100%;
            background: #f9fafb;
        }
    </style>
</head>

<body class="site-public guest-auth font-sans antialiased">

@if ($wide)
    <main class="min-h-dvh flex justify-center px-4 py-6 sm:px-6 sm:py-8">
        <div class="guest-auth-card w-full max-w-[48rem] bg-white rounded-2xl shadow-sm border border-gray-100 px-6 sm:px-8 py-6">
            <div class="flex flex-col items-center mb-4">
                <a href="{{ url('/') }}" title="Retour à l'accueil">
                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                         class="w-14 h-14 object-contain mb-3 hover:opacity-80 transition-opacity"
                         alt="Logo DPC">
                </a>
                <p class="text-gray-700 text-sm font-medium tracking-wide uppercase text-center">
                    Direction de la Pension Civile
                </p>
            </div>
            {{ $slot }}
        </div>
    </main>
@else
    <div class="guest-auth-shell">
        <div @class(['guest-auth-form', 'guest-auth-form--no-scroll' => $noScroll])>
            <div class="guest-auth-form-inner">
                <div @class([
                    'w-full min-w-0 overflow-x-hidden bg-white rounded-2xl shadow-sm border border-gray-100 px-8',
                    'max-w-md',
                    'py-5' => $noScroll,
                    'py-10' => ! $noScroll,
                ])>
                    <div class="flex flex-col items-center {{ $noScroll ? 'mb-4' : 'mb-8' }}">
                        <a href="{{ url('/') }}" title="Retour à l'accueil">
                            <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                                 class="{{ $noScroll ? 'w-14 h-14' : 'w-20 h-20' }} object-contain {{ $noScroll ? 'mb-2' : 'mb-3' }} hover:opacity-80 transition-opacity"
                                 alt="Logo DPC">
                        </a>
                        <p class="text-gray-700 text-sm font-medium tracking-wide uppercase text-center">
                            Direction de la Pension Civile
                        </p>
                    </div>
                    {{ $slot }}
                </div>

                @if ($showCopyright)
                    <p class="guest-auth-copy text-sm text-gray-600 text-center">
                        &copy; {{ date('Y') }} Direction de la Pension Civile — République d'Haïti
                    </p>
                @endif
            </div>
        </div>
    </div>
@endif

</body>
</html>
