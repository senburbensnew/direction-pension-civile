    <div class="relative text-white z-[1001] bg-[#173052] bg-motif-dots">
        <div class="site-gutter py-4 relative z-10">
            <div class="grid w-full grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-3 lg:flex lg:items-center lg:justify-between lg:gap-6">
                <!-- Logo -->
                <div class="col-start-1 row-start-1 flex min-w-0 items-center lg:shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <x-optimized-image
                            src="images/setting-logo-1-M13oPLiYoM.png"
                            alt="Logo"
                            width="64"
                            height="64"
                            class="w-10 h-10 md:w-16 md:h-16 transition-transform group-hover:scale-105"
                            loading="eager"
                        />
                        <div class="flex flex-col min-w-0">
                            <span class="text-base md:text-lg font-semibold tracking-tight leading-tight group-hover:text-orange-500 transition-colors">
                                {{ __('messages.direction') }}
                            </span>
                            <span class="text-base font-normal tracking-wide text-white/80 leading-tight">
                                {{ __('messages.republic') }}
                            </span>
                        </div>
                    </a>
                </div>

                <div class="contents lg:flex lg:items-center lg:gap-6 lg:shrink-0">
                <nav class="col-span-2 row-start-2 flex w-full flex-wrap justify-center gap-x-4 gap-y-2 border-t border-white/20 pt-3 text-base font-medium lg:w-auto lg:border-0 lg:pt-0 lg:justify-end">
                    <a href="{{ route('home') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.home') }}
                    </a>
                    <a href="{{ route('faq.index') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.faq') }}
                    </a>
                    <a href="{{ route('glossaire') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.glossaire') }}
                    </a>
                    <a href="{{ route('contact') }}"
                       class="hover:text-orange-500 transition-colors py-1 border-b-2 border-transparent hover:border-orange-500">
                        {{ __('messages.contact') }}
                    </a>
                </nav>

                <div class="col-start-2 row-start-1 flex shrink-0 items-center justify-end gap-3 sm:gap-4">
                        @auth
                            <x-notification-bell-dropdown theme="dark" />
                        @endauth

                        @guest
                            <a href="{{ route('login') }}" class="inline-block">
                                <button class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded transition-colors text-base shadow-md hover:shadow-lg">
                                    {{ __('messages.login') }}
                                </button>
                            </a>
                        @else
                            <x-user-account-dropdown theme="dark" />
                        @endguest

                        <div class="flex items-center gap-1.5 opacity-75 hover:opacity-100 transition-opacity">
                            @if (App::getLocale() !== 'fr')
                                <a href="{{ route('locale', 'fr') }}" class="rounded p-0.5 opacity-70 hover:opacity-100 transition-opacity" title="Français">
                                    <img src="{{ asset('images/france-flag-icon.svg') }}" alt="French Flag" class="w-3.5 h-3.5 rounded-sm">
                                </a>
                            @endif

                            @if (App::getLocale() !== 'ht')
                                <a href="{{ route('locale', 'ht') }}" class="rounded p-0.5 opacity-70 hover:opacity-100 transition-opacity" title="Kreyòl">
                                    <img src="{{ asset('images/haiti-flag-icon.svg') }}" alt="Haitian Flag Flag" class="w-3.5 h-3.5 rounded-sm">
                                </a>
                            @endif
                        </div>
                </div>
                </div>
            </div>
        </div>
    </div>
