<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="site-public-root guest-auth-page" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <x-fonts />
    <!-- <link href="{{ asset('build/assets/app-bInZ0-a9.css') }}" rel="stylesheet"> -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html.guest-auth-page,
        html.guest-auth-page body.guest-auth {
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
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100dvh;
            max-height: 100dvh;
            min-height: 0;
            overflow: hidden;
        }
        .guest-auth-photo {
            display: none;
            flex: 0 0 50%;
            width: 50%;
            min-height: 0;
            align-self: stretch;
            position: relative;
            overflow: hidden;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            padding: 2.5rem 2rem;
            background: #1e3a8a;
        }
        .guest-auth-photo img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 18%;
        }
        .guest-auth-photo::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(30, 64, 175, 0.58) 0%, rgba(37, 99, 235, 0.42) 46%, rgba(23, 48, 82, 0.72) 100%);
            z-index: 1;
        }
        .guest-auth-photo p {
            position: relative;
            z-index: 2;
            margin: 0;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 500;
            text-align: center;
            text-shadow: 0 1px 3px rgba(15, 23, 42, 0.45);
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
            background: #f9fafb;
        }
        /* Equal spacers center a short form. They shrink to nothing when the
           form is taller than the pane, so the scroll starts at the first field. */
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
        @media (min-width: 1024px) {
            .guest-auth-photo { display: flex; }
        }
    </style>
</head>

<body class="site-public guest-auth font-sans antialiased">

    <div class="guest-auth-shell">

        <div class="guest-auth-form">
            <div class="guest-auth-form-inner {{ $wide ? 'is-wide' : '' }}">
                <div class="w-full min-w-0 overflow-x-hidden {{ $wide ? 'max-w-3xl' : 'max-w-md' }} bg-white rounded-2xl shadow-sm border border-gray-100 px-8 {{ $wide ? 'py-6' : 'py-10' }}"
                     @if ($wide) style="max-width: 48rem;" @endif>
                    <div class="flex flex-col items-center {{ $wide ? 'mb-4' : 'mb-8' }}">
                        <a href="{{ url('/') }}" title="Retour à l'accueil">
                            <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                                 class="{{ $wide ? 'w-14 h-14' : 'w-20 h-20' }} object-contain mb-3 hover:opacity-80 transition-opacity"
                                 alt="Logo DPC">
                        </a>
                        <p class="text-gray-700 text-sm font-medium tracking-wide uppercase text-center">
                            Direction de la Pension Civile
                        </p>
                    </div>
                    {{ $slot }}
                </div>

                <p class="guest-auth-copy text-sm text-gray-600 text-center">
                    &copy; {{ date('Y') }} Direction de la Pension Civile — République d'Haïti
                </p>
            </div>
        </div>
    </div>
    <!-- <script src="{{ asset('build/assets/app-BiTlx0PY.js') }}"></script> -->
</body>
</html>
