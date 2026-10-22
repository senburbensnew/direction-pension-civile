<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      @class([
          'site-public-root text-base bg-gray-50',
          'h-dvh max-h-dvh overflow-hidden' => ! $wide,
          'min-h-full h-auto overflow-x-hidden overflow-y-auto' => $wide,
      ])
      data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-telephone-validation-meta />
    <title>{{ config('app.name', 'Laravel') }}</title>
    <x-site-head-meta />
    @stack('meta')
    @if ($showPhoto)
        @php
            $guestHeroMedia = \App\Support\PublicMedia::forUploadedOrStatic('cms/institution/guest-auth-pension-couple.jpg');
            $guestHeroPreloadHref = $guestHeroMedia['webp'] ?? $guestHeroMedia['url'];
            $guestHeroPreloadSrcset = $guestHeroMedia['webp_srcset'] ?? $guestHeroMedia['srcset'] ?? null;
            $guestHeroSizes = '(min-width: 768px) 50vw, 1px';
        @endphp
        <link
            rel="preload"
            as="image"
            media="(min-width: 768px)"
            href="{{ $guestHeroPreloadHref }}"
            @if ($guestHeroPreloadSrcset)
                imagesrcset="{{ $guestHeroPreloadSrcset }}"
                imagesizes="{{ $guestHeroSizes }}"
            @endif
            type="{{ $guestHeroMedia['webp'] ? 'image/webp' : 'image/jpeg' }}"
        >
    @endif

    <x-fonts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body @class([
    'site-public guest-auth m-0 font-sans antialiased bg-gray-50',
    'h-dvh max-h-dvh overflow-hidden' => ! $wide,
    'min-h-full h-auto overflow-x-hidden overflow-y-auto' => $wide,
])>

@php($guestCopyrightLine = __('guest.layout.copyright', ['year' => date('Y')]))

<div @class([
    'flex w-full min-h-0 items-stretch bg-gray-50',
    'fixed inset-0 h-dvh max-h-dvh overflow-hidden' => ! $wide,
    'relative min-h-dvh overflow-visible max-lg:min-h-dvh max-lg:h-auto max-lg:overflow-visible' => $wide,
    'lg:h-dvh lg:max-h-dvh lg:overflow-x-hidden lg:overflow-y-auto lg:overscroll-auto lg:[-webkit-overflow-scrolling:touch]' => $wide,
])>
    @if ($showPhoto)
        <aside
            aria-hidden="true"
            @class([
                'relative hidden min-h-0 w-1/2 flex-[0_0_50%] flex-col items-center justify-end overflow-hidden self-stretch bg-blue-800 p-8 px-8 md:flex',
                'self-start' => $wide,
                'lg:sticky lg:top-0 lg:h-dvh' => $wide,
            ])>
            <div class="absolute inset-0 z-0 [&_picture]:absolute [&_picture]:inset-0 [&_picture]:h-full [&_picture]:w-full [&_img]:h-full [&_img]:w-full [&_img]:object-cover [&_img]:object-center">
                <x-optimized-image
                    src="cms/institution/guest-auth-pension-couple.jpg"
                    alt=""
                    sizes="{{ $guestHeroSizes ?? '(min-width: 768px) 50vw, 1px' }}"
                    :width="$guestHeroMedia['width'] ?? null"
                    :height="$guestHeroMedia['height'] ?? null"
                    loading="lazy"
                />
            </div>
            <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-b from-blue-800/60 via-blue-600/45 to-slate-900/70"></div>
            {{-- <p class="relative z-[2] m-0 max-w-[18rem] text-center text-sm font-medium leading-snug text-white drop-shadow-md lg:text-[0.875rem]">
                {{ $guestCopyrightLine }}
            </p> --}}
        </aside>
    @endif

    <div @class([
        'relative flex min-h-0 min-w-0 flex-1 flex-col items-center overflow-x-hidden bg-gray-50',
        'max-h-full overflow-y-auto overscroll-contain' => ! $wide && ! $noScroll,
        'max-h-none overflow-y-visible overscroll-auto' => $wide,
        'justify-center overflow-y-hidden' => $noScroll,
        'before:min-h-0 before:flex-1 before:basis-0 before:content-[""] after:min-h-0 after:flex-1 after:basis-0 after:content-[""]' => ! $wide && ! $noScroll,
        'before:hidden after:hidden' => $wide || $noScroll,
    ])>
        <div @class([
            'box-border flex w-full min-w-0 max-w-full flex-none flex-col items-center justify-start px-6',
            'max-w-[min(80rem,calc(100vw-2rem))]' => $wide,
            'pb-3 pt-6' => ! $noScroll,
            'pb-1 pt-3' => $noScroll,
        ])>
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

                    <div class="flex flex-wrap items-center justify-end gap-1" aria-label="{{ __('guest.layout.language') }}">
                        @if ($locale === 'fr')
                            <span class="inline-flex cursor-default items-center gap-1 rounded-md bg-gray-100/90 px-1.5 py-0.5 text-[10px] font-medium leading-none text-gray-600 ring-1 ring-inset ring-gray-200/80">
                                <img src="{{ asset('images/france-flag-icon.svg') }}" alt="" aria-hidden="true" class="h-3 w-3 shrink-0 rounded-sm object-cover opacity-90">
                                {{ __('guest.layout.french') }}
                            </span>
                        @else
                            <a href="{{ route('locale', 'fr') }}" title="{{ __('guest.layout.french') }}" class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-normal leading-none text-gray-400 transition-colors hover:bg-gray-50 hover:text-gray-600">
                                <img src="{{ asset('images/france-flag-icon.svg') }}" alt="" aria-hidden="true" class="h-3 w-3 shrink-0 rounded-sm object-cover opacity-70">
                                {{ __('guest.layout.french') }}
                            </a>
                        @endif

                        @if ($locale === 'ht')
                            <span class="inline-flex cursor-default items-center gap-1 rounded-md bg-gray-100/90 px-1.5 py-0.5 text-[10px] font-medium leading-none text-gray-600 ring-1 ring-inset ring-gray-200/80">
                                <img src="{{ asset('images/ht-flag.PNG') }}" alt="" aria-hidden="true" class="h-3 w-3 shrink-0 rounded-sm object-cover opacity-90">
                                {{ __('guest.layout.creole') }}
                            </span>
                        @else
                            <a href="{{ route('locale', 'ht') }}" title="{{ __('guest.layout.creole') }}" class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-normal leading-none text-gray-400 transition-colors hover:bg-gray-50 hover:text-gray-600">
                                <img src="{{ asset('images/ht-flag.PNG') }}" alt="" aria-hidden="true" class="h-3 w-3 shrink-0 rounded-sm object-cover opacity-70">
                                {{ __('guest.layout.creole') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @if ($showCopyright)
                <p @class([
                    'm-0 shrink-0 text-center text-xs text-gray-500 sm:text-sm',
                    'pt-3 pb-0.5' => ! $noScroll,
                    'pt-2' => $noScroll,
                ])>
                    {{ $guestCopyrightLine }}
                </p>
            @endif
        </div>
    </div>
</div>

<x-flash-messages position="top-6" />

@stack('scripts')
</body>
</html>
