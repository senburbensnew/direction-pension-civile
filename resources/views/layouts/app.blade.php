<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="site-public-root" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-telephone-validation-meta />

    <title>{{ config('app.name', 'Portail DPC') }} — Direction de la Pension Civile</title>
    <x-site-head-meta />

    <x-fonts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: true,
        init() {
            try {
                if (window.innerWidth >= 1024 && localStorage.getItem('portal-sidebar-collapsed') === '0') {
                    this.sidebarCollapsed = false;
                }
            } catch (e) {}
        },
        toggleCollapse() {
            this.sidebarCollapsed = ! this.sidebarCollapsed;
            try {
                localStorage.setItem('portal-sidebar-collapsed', this.sidebarCollapsed ? '1' : '0');
            } catch (e) {}
        },
    }"
    x-on:keydown.escape.window="sidebarOpen = false"
    x-on:resize.window="if (window.innerWidth >= 1024) sidebarOpen = false"
    :data-collapsed="sidebarCollapsed ? 'true' : 'false'"
    class="group/body site-public w-full min-h-dvh font-sans antialiased bg-[#e6ebf1] text-gray-800 overflow-x-hidden">

    <noscript>
        <div class="fixed inset-0 z-[10001] flex items-center justify-center bg-black/80">
            <div class="bg-white max-w-md w-full mx-4 p-6 rounded-xl shadow-xl text-center">
                <div class="text-red-500 text-5xl mb-4"><i class="fas fa-exclamation-triangle"></i></div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">JavaScript désactivé</h2>
                <p class="text-gray-600">Ce site nécessite JavaScript pour fonctionner correctement.</p>
            </div>
        </div>
    </noscript>

    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 bg-black/50 z-40 lg:hidden"
        x-on:click="sidebarOpen = false"></div>

    <aside
        id="portal-sidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col bg-[#0f2340] shadow-lg transition-all duration-200 ease-out"
        :class="[
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            sidebarCollapsed ? 'lg:w-[4.25rem]' : 'lg:w-60',
        ]">
        @include('layouts.partials.app-sidebar')
    </aside>

    <div
        id="portal-main"
        class="flex flex-col min-h-dvh min-w-0 bg-white transition-[margin] duration-200"
        :class="sidebarCollapsed ? 'lg:ml-[4.25rem]' : 'lg:ml-60'">

        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-start sm:items-center justify-between gap-3 px-4 py-3">
                <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1">
                    <button type="button" x-on:click="sidebarOpen = !sidebarOpen"
                        class="text-gray-500 hover:text-gray-700 lg:hidden mt-0.5 flex-shrink-0" aria-label="Menu">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <div class="min-w-0 flex-1">
                        @isset($header)
                            {{ $header }}
                        @else
                            <p class="text-sm font-medium text-gray-700">{{ config('app.name', 'Portail DPC') }}</p>
                        @endisset
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                    <div class="hidden sm:flex items-center gap-1.5 opacity-80 hover:opacity-100 transition-opacity">
                        @if (App::getLocale() !== 'fr')
                            <a href="{{ route('locale', 'fr') }}" class="rounded p-0.5 opacity-70 hover:opacity-100 transition-opacity" title="Français">
                                <img src="{{ asset('images/france-flag-icon.svg') }}" alt="" class="w-3.5 h-3.5 rounded-sm">
                            </a>
                        @endif
                        @if (App::getLocale() !== 'ht')
                            <a href="{{ route('locale', 'ht') }}" class="rounded p-0.5 opacity-70 hover:opacity-100 transition-opacity" title="Kreyòl">
                                <img src="{{ asset('images/haiti-flag-icon.svg') }}" alt="" class="w-3.5 h-3.5 rounded-sm">
                            </a>
                        @endif
                    </div>

                    @auth
                        <x-notification-bell-dropdown theme="light" />
                        <x-user-account-dropdown theme="light" />
                    @endauth

                    <a href="{{ route('home') }}" target="_blank" rel="noopener"
                       class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        <span class="hidden sm:inline">Site public</span>
                    </a>
                </div>
            </div>
        </header>

        @php
            $portalMainWide = request()->routeIs('demandes.rencontre.visio');
        @endphp
        <main @class([
            'flex-1 w-full min-w-0 min-h-0 pb-10',
            'max-w-none px-3 sm:px-5 lg:px-6' => $portalMainWide,
        ])>
            {{ $slot }}
        </main>
    </div>

    <x-flash-messages />

    @stack('scripts')
</body>
</html>
