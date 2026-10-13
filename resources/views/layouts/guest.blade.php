<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      @class(['site-public-root', 'guest-auth-page', 'guest-auth-locked' => ! $wide])
      data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @stack('meta')
    @if ($showPhoto)
        @php
            $guestHeroMedia = \App\Support\PublicMedia::forPublicPath('images/guest-auth-pension-couple.jpg');
        @endphp
        <link rel="preload" as="image" href="{{ $guestHeroMedia['webp'] ?? $guestHeroMedia['url'] }}" type="{{ $guestHeroMedia['webp'] ? 'image/webp' : 'image/jpeg' }}">
    @endif

    <x-fonts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- <link href="{{ asset('build/assets/app-DH4eVY7K.css') }}" rel="stylesheet"> -->
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
            width: 100%;
            min-height: 0;
            background: #f9fafb;
        }

        html.guest-auth-locked .guest-auth-shell {
            position: fixed;
            inset: 0;
            height: 100dvh;
            max-height: 100dvh;
            overflow: hidden;
        }

        html.guest-auth-page:not(.guest-auth-locked) .guest-auth-shell {
            position: relative;
            min-height: 100dvh;
            overflow: visible;
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

        html.guest-auth-page:not(.guest-auth-locked) .guest-auth-photo {
            align-self: flex-start;
        }

        .guest-auth-photo picture,
        .guest-auth-photo img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
        }

        .guest-auth-photo picture img {
            position: static;
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
            max-width: 18rem;
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 500;
            line-height: 1.45;
            text-align: center;
            text-shadow: 0 1px 3px rgba(15, 23, 42, 0.45);
        }

        @media (min-width: 1024px) {
            .guest-auth-photo p {
                font-size: 0.875rem;
            }
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
            position: relative;
        }

        html.guest-auth-page:not(.guest-auth-locked) .guest-auth-form {
            max-height: none;
            overflow-y: visible;
            overscroll-behavior: auto;
        }

        .guest-auth-form::before,
        .guest-auth-form::after {
            content: "";
            flex: 1 1 0;
            min-height: 0;
        }

        html.guest-auth-page:not(.guest-auth-locked) .guest-auth-form::before,
        html.guest-auth-page:not(.guest-auth-locked) .guest-auth-form::after {
            display: none;
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

        .guest-auth-form-inner.is-wide {
            width: 100%;
            max-width: min(80rem, calc(100vw - 2rem));
        }

        .guest-auth-copy {
            margin: 0;
            padding: 0.75rem 0 0.15rem;
            flex-shrink: 0;
        }

        .guest-lang-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.25;
            border: 1px solid #e5e7eb;
            color: #374151;
            background: #fff;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        a.guest-lang-pill:hover {
            border-color: #173052;
            background: #f9fafb;
        }

        .guest-lang-pill--active {
            border-color: #173052;
            background: #eef2f7;
            color: #173052;
            cursor: default;
        }

        .guest-lang-pill img {
            width: 1rem;
            height: 1rem;
            border-radius: 2px;
            object-fit: cover;
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

        @media (min-width: 768px) {
            .guest-auth-photo {
                display: flex;
            }
        }

        @media (min-width: 1024px) {
            html.guest-auth-page:not(.guest-auth-locked) .guest-auth-photo {
                position: sticky;
                top: 0;
                height: 100dvh;
            }

            /*
             * Demande de compte (wide) : le shell défile (trackpad / deux doigts
             * sur toute la page) ; la photo reste sticky à gauche.
             */
            html.guest-auth-page:not(.guest-auth-locked) .guest-auth-shell {
                height: 100dvh;
                max-height: 100dvh;
                overflow-x: hidden;
                overflow-y: auto;
                overscroll-behavior: auto;
                -webkit-overflow-scrolling: touch;
            }

            html.guest-auth-page:not(.guest-auth-locked) .guest-auth-form {
                max-height: none;
                overflow-y: visible;
            }
        }

        /* Demande de compte : page fluide (mobile / tablette) */
        html.guest-auth-page:not(.guest-auth-locked),
        html.guest-auth-page:not(.guest-auth-locked) body.guest-auth {
            margin: 0;
            min-height: 100%;
            height: auto;
            overflow-x: hidden;
            overflow-y: auto;
            background: #f9fafb;
        }

        @media (max-width: 1023px) {
            html.guest-auth-page:not(.guest-auth-locked) .guest-auth-shell {
                min-height: 100dvh;
                height: auto;
                overflow: visible;
            }

            html.guest-auth-page:not(.guest-auth-locked) .guest-auth-form {
                max-height: none;
                overflow-y: visible;
            }
        }
    </style>
</head>

<body class="site-public guest-auth font-sans antialiased">

@php($guestCopyrightLine = __('guest.layout.copyright', ['year' => date('Y')]))

<div class="guest-auth-shell">
    @if ($showPhoto)
        <aside class="guest-auth-photo" aria-hidden="true">
            <x-optimized-image
                src="images/guest-auth-pension-couple.jpg"
                alt=""
                priority
            />
            <p>{{ $guestCopyrightLine }}</p>
        </aside>
    @endif

    <div @class(['guest-auth-form', 'guest-auth-form--no-scroll' => $noScroll])>
        <div class="guest-auth-form-inner {{ $wide ? 'is-wide' : '' }}">
            <div @class([
                'relative w-full min-w-0 overflow-x-hidden bg-white rounded-2xl shadow-sm border border-gray-100 px-8',
                'max-w-[44rem]' => $wide,
                'max-w-md' => ! $wide,
                'py-6' => $wide,
                'py-5' => ! $wide && $noScroll,
                'py-10' => ! $wide && ! $noScroll,
                'px-6 sm:px-8' => $wide,
            ])>
                <div class="flex flex-col items-center {{ $wide ? 'mb-4' : ($noScroll ? 'mb-4' : 'mb-8') }}">
                    <a href="{{ url('/') }}" title="{{ __('guest.layout.back_home_title') }}">
                        <x-optimized-image
                            src="images/setting-logo-1-M13oPLiYoM.png"
                            alt="{{ __('guest.layout.logo_alt') }}"
                            class="{{ $wide || $noScroll ? 'w-14 h-14' : 'w-20 h-20' }} object-contain {{ $noScroll ? 'mb-2' : 'mb-3' }} hover:opacity-80 transition-opacity"
                            loading="eager"
                        ></x-optimized-image>
                    </a>
                    <p class="text-gray-700 text-sm font-medium tracking-wide uppercase text-center">
                        {{ __('guest.layout.direction') }}
                    </p>
                </div>
                {{ $slot }}

                @php($locale = app()->getLocale())
                <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-x-3 gap-y-2">
                    <a
                        href="{{ url('/') }}"
                        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-[#173052] font-medium transition-colors"
                    >
                        <i class="fas fa-home text-xs" aria-hidden="true"></i>
                        <span>{{ __('guest.layout.back_home') }}</span>
                    </a>

                    <div class="flex flex-wrap items-center justify-end gap-2" aria-label="{{ __('guest.layout.language') }}">
                        @if ($locale === 'fr')
                            <span class="guest-lang-pill guest-lang-pill--active">
                                <img src="{{ asset('images/france-flag-icon.svg') }}" alt="" aria-hidden="true">
                                {{ __('guest.layout.french') }}
                            </span>
                        @else
                            <a href="{{ route('locale', 'fr') }}" class="guest-lang-pill" title="{{ __('guest.layout.french') }}">
                                <img src="{{ asset('images/france-flag-icon.svg') }}" alt="" aria-hidden="true">
                                {{ __('guest.layout.french') }}
                            </a>
                        @endif

                        @if ($locale === 'ht')
                            <span class="guest-lang-pill guest-lang-pill--active">
                                <img src="{{ asset('images/ht-flag.PNG') }}" alt="" aria-hidden="true">
                                {{ __('guest.layout.creole') }}
                            </span>
                        @else
                            <a href="{{ route('locale', 'ht') }}" class="guest-lang-pill" title="{{ __('guest.layout.creole') }}">
                                <img src="{{ asset('images/ht-flag.PNG') }}" alt="" aria-hidden="true">
                                {{ __('guest.layout.creole') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @if ($showCopyright)
                <p class="guest-auth-copy text-xs sm:text-sm text-gray-500 text-center">
                    {{ $guestCopyrightLine }}
                </p>
            @endif
        </div>
    </div>
</div>
@stack('scripts')
<!-- <script src="{{ asset('build/assets/app-CKnLtXCO.js') }}"></script> -->
</body>
</html>
