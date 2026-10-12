<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="site-public-root" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Portail DPC') }} — Direction de la Pension Civile</title>

    <x-fonts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --portal-sidebar-w: 15rem;
            --portal-sidebar-w-collapsed: 4.25rem;
            --portal-sidebar-active: var(--portal-sidebar-w);
        }
        body.portal-sidebar-collapsed {
            --portal-sidebar-active: var(--portal-sidebar-w-collapsed);
        }

        #portal-sidebar {
            width: var(--portal-sidebar-active);
            transform: translateX(calc(-1 * var(--portal-sidebar-w)));
            transition: transform .25s ease, width .2s ease;
        }
        @media (min-width: 1024px) {
            #portal-sidebar { transform: translateX(0); }
            #portal-main     { margin-left: var(--portal-sidebar-active); transition: margin-left .2s ease; }
            #portal-overlay  { display: none !important; }
        }
        body.portal-sidebar-open #portal-sidebar  { transform: translateX(0); }
        body.portal-sidebar-open #portal-overlay  { display: block; }

        .portal-nav-link {
            display: flex; align-items: center; gap: .625rem;
            padding: .45rem .65rem; border-radius: .4rem;
            color: #94a3b8; font-size: .8rem; font-weight: 500;
            transition: background .15s, color .15s;
            min-height: 2.25rem;
        }
        .portal-nav-link:hover { background: rgba(255,255,255,.07); color: #e2e8f0; }
        .portal-nav-link.active { background: rgba(255,255,255,.12); color: #fff; }
        .portal-nav-link i { width: 1.25rem; text-align: center; font-size: .85rem; flex-shrink: 0; }

        body.portal-sidebar-collapsed .portal-sidebar-label { display: none; }
        body.portal-sidebar-collapsed .portal-nav-link { justify-content: center; padding-left: .5rem; padding-right: .5rem; }
        body.portal-sidebar-collapsed .portal-sidebar-brand { justify-content: center; padding-left: .5rem; padding-right: .5rem; }
        body.portal-sidebar-collapsed .portal-sidebar-brand a { flex: 0; }
        body.portal-sidebar-collapsed .portal-nav-divider { margin-left: .5rem; margin-right: .5rem; }
        body.portal-sidebar-collapsed .portal-sidebar-user { display: none; }
        body.portal-sidebar-collapsed .portal-sidebar-collapsed-only { display: block; }
        body.portal-sidebar-collapsed .portal-collapse-icon-expanded { display: none; }
        body.portal-sidebar-collapsed .portal-collapse-icon-collapsed { display: inline; }

        .portal-sidebar-collapsed-only { display: none; }
        .portal-collapse-icon-collapsed { display: none; }

        /* Barres fixées en bas : alignées sur la zone principale (pas sous la sidebar). */
        .portal-fixed-bottom {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 30;
            transition: left .2s ease;
        }
        @media (min-width: 1024px) {
            .portal-fixed-bottom {
                left: var(--portal-sidebar-active);
            }
        }
    </style>
    @stack('styles')
</head>

<body class="site-public portal-sidebar-collapsed w-full min-h-dvh font-sans antialiased bg-[#e6ebf1] text-gray-800 overflow-x-hidden">

    <noscript>
        <div class="fixed inset-0 z-[10001] flex items-center justify-center bg-black/80">
            <div class="bg-white max-w-md w-full mx-4 p-6 rounded-xl shadow-xl text-center">
                <div class="text-red-500 text-5xl mb-4"><i class="fas fa-exclamation-triangle"></i></div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">JavaScript désactivé</h2>
                <p class="text-gray-600">Ce site nécessite JavaScript pour fonctionner correctement.</p>
            </div>
        </div>
    </noscript>

    <div id="portal-overlay" class="hidden fixed inset-0 bg-black/50 z-40 lg:hidden" onclick="closePortalSidebar()"></div>

    <aside id="portal-sidebar" class="fixed inset-y-0 left-0 z-50 flex flex-col shadow-lg" style="background: #0f2340;">
        @include('layouts.partials.app-sidebar')
    </aside>

    <div id="portal-main" class="flex flex-col min-h-dvh min-w-0 bg-white">

        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-start sm:items-center justify-between gap-3 px-4 py-3">
                <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1">
                    <button type="button" onclick="togglePortalSidebar()"
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
                    <div class="hidden sm:flex items-center gap-2">
                        @if (App::getLocale() !== 'fr')
                            <a href="{{ route('locale', 'fr') }}" class="hover:opacity-75" title="Français">
                                <img src="{{ asset('images/france-flag-icon.svg') }}" alt="" class="w-4 h-4 rounded-sm">
                            </a>
                        @endif
                        @if (App::getLocale() !== 'ht')
                            <a href="{{ route('locale', 'ht') }}" class="hover:opacity-75" title="Kreyòl">
                                <img src="{{ asset('images/haiti-flag-icon.svg') }}" alt="" class="w-4 h-4 rounded-sm">
                            </a>
                        @endif
                    </div>

                    @auth
                        @php $portalUnread = auth()->user()->unreadNotifications()->count(); @endphp
                        <a href="{{ route('notifications.index') }}"
                           class="relative text-gray-500 hover:text-gray-700 p-1"
                           title="Notifications">
                            <i class="fas fa-bell text-lg"></i>
                            @if($portalUnread > 0)
                                <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] rounded-full min-w-[1rem] h-4 px-1 flex items-center justify-center leading-none">
                                    {{ $portalUnread > 9 ? '9+' : $portalUnread }}
                                </span>
                            @endif
                        </a>

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

        <x-pension-context-banner />

        @php
            $showGlobalFlash = ! request()->routeIs('demandes.rencontre.create');
            $hasGlobalFlash = $showGlobalFlash && (
                session('success') || session('error') || session('warning')
            );
            $portalMainWide = request()->routeIs('demandes.rencontre.visio');
        @endphp
        <main @class([
            'flex-1 w-full min-h-0 pb-10',
            'max-w-none px-3 sm:px-5 lg:px-6' => $portalMainWide,
        ])>
            @if($hasGlobalFlash)
                <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-3 pb-2 space-y-2">
                    @if(session('success'))
                        <div class="flex items-start gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-2.5 text-sm">
                            <i class="fas fa-check-circle text-green-500 flex-shrink-0 mt-0.5"></i>
                            <div class="min-w-0 space-y-1">
                                <p>{{ session('success') }}</p>
                                @if(session('created_password'))
                                    <p class="font-mono text-sm">
                                        Mot de passe provisoire :
                                        <strong>{{ session('created_password') }}</strong>
                                    </p>
                                    <p class="text-xs text-green-700">
                                        Communiquez-le au demandeur (affichage unique sur cette page).
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="flex items-center gap-2 bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-2.5 text-sm">
                            <i class="fas fa-exclamation-circle text-red-500 flex-shrink-0"></i>
                            {{ session('error') }}
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="flex items-center gap-2 bg-amber-50 border border-amber-300 text-amber-900 rounded-lg px-4 py-2.5 text-sm">
                            <i class="fas fa-exclamation-triangle text-amber-500 flex-shrink-0"></i>
                            {{ session('warning') }}
                        </div>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <script>
        (function () {
            var key = 'portal-sidebar-collapsed';
            try {
                if (window.innerWidth >= 1024 && localStorage.getItem(key) === '0') {
                    document.body.classList.remove('portal-sidebar-collapsed');
                }
            } catch (e) {}
        })();

        function togglePortalSidebar() {
            document.body.classList.toggle('portal-sidebar-open');
        }
        function closePortalSidebar() {
            document.body.classList.remove('portal-sidebar-open');
        }
        function togglePortalSidebarCollapse() {
            document.body.classList.toggle('portal-sidebar-collapsed');
            localStorage.setItem(
                'portal-sidebar-collapsed',
                document.body.classList.contains('portal-sidebar-collapsed') ? '1' : '0'
            );
        }
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                closePortalSidebar();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
