<!-- Main Footer Section -->
<div class="container mx-auto bg-[#173052] bg-motif-dots py-6 px-4 sm:px-6 lg:px-8 text-white border-t-[3px] border-orange-500">
    <div class="flex flex-col md:flex-row justify-between items-start md:space-x-6 lg:space-x-8 space-y-8 md:space-y-0">
        <!-- LA DIRECTION Column -->
        <div class="w-full md:w-1/3">
            <h3 class="font-bold text-lg md:text-xl mb-3 border-b-2 border-white pb-2">{{ __('messages.who_are_we') }}
            </h3>
            <ul class="space-y-2">
                <li><a href="{{ route('quisommesnous.missions') }}"
                        class="hover:text-orange-500 transition">{{ __('messages.mission_and_responsibilities') }}</a>
                </li>
                <li><a href="{{ route('quisommesnous.structure-organique') }}"
                        class="hover:text-orange-500 transition">{{ __('messages.organizational_structure') }}</a></li>
                <li><a href="{{ route('quisommesnous.mots', ['role' => 'ministre']) }}"
                        class="hover:text-orange-500 transition">Mots du Ministre</a></li>
            </ul>
        </div>

                <!-- DIRECTIONS ET SERVICES Column -->
        <div class="w-full md:w-1/3">
            <h3 class="font-bold text-lg md:text-xl mb-3 border-b-2 border-white pb-2">Directions et Services
            </h3>
            @php
                $footerServices = \App\Models\Service::publicOrdered();
                $footerDirections = \App\Models\DirectionDepartementale::ordered()->get();
            @endphp
            <p class="text-sm font-semibold text-white/80 mb-2">Services</p>
            <ul class="space-y-2 mb-5">
                @foreach($footerServices as $footerService)
                <li>
                    <a href="{{ route('services.show', $footerService) }}"
                       class="hover:text-orange-500 transition">{{ $footerService->nom }}</a>
                </li>
                @endforeach
            </ul>
            <p class="text-sm font-semibold text-white/80 mb-2">Directions départementales</p>
            <ul class="space-y-2">
                @foreach($footerDirections as $footerDirection)
                <li>
                    <a href="{{ route('directions.show', $footerDirection) }}"
                       class="hover:text-orange-500 transition">{{ $footerDirection->nom }} ({{ $footerDirection->abbr }})</a>
                </li>
                @endforeach
            </ul>
        </div>

        <!-- LIENS UTILES Column -->
        <div class="w-full md:w-1/3">
            <h3 class="font-bold text-lg md:text-xl mb-3 border-b-2 border-white pb-2">{{ __('messages.useful_links') }}
            </h3>
            <ul class="space-y-2">
                <li><a href="https://mef.gouv.ht/" target="_blank"
                        class="hover:text-orange-500 transition">{{ __('messages.MEF') }}</a>
                </li>
                <li><a href="https://budget.gouv.ht/" target="_blank"
                        class="hover:text-orange-500 transition">{{ __('messages.DGB') }}</a></li>
                <li><a href="https://www.douane.gouv.ht/" target="_blank"
                        class="hover:text-orange-500 transition">{{ __('messages.AGD') }}</a></li>
                <li><a href="https://www.brh.ht/" target="_blank" class="hover:text-orange-500 transition">{{ __('messages.BRH') }}</a>
                </li>
                <li><a href="https://bmpad.gouv.ht/" target="_blank"
                        class="hover:text-orange-500 transition">{{ __('messages.BMPAD') }}</a></li>
                <li><a href="https://dgi.gouv.ht/" target="_blank"
                        class="hover:text-orange-500 transition">{{ __('messages.DGI') }}</a></li>
                <li><a href="https://igf.gouv.ht/" target="_blank"
                        class="hover:text-orange-500 transition">{{ __('messages.IGF') }}</a></li>
                <li><a href="https://ihsi.gouv.ht/" target="_blank"
                        class="hover:text-orange-500 transition">{{ __('messages.IHSI') }}</a></li>
                <li><a href="https://oavct.gouv.ht/" target="_blank"
                        class="hover:text-orange-500 transition">{{ __('messages.OAVCT') }}</a></li>
                <li><a href="https://sonapi.gouv.ht/" target="_blank"
                        class="hover:text-orange-500 transition">{{ __('messages.SONAPI') }}</a></li>
                <li><a href="https://www.bnconline.com/" target="_blank"
                        class="hover:text-orange-500 transition">{{ __('messages.BNC') }}</a></li>
            </ul>
        </div>

        <!-- CONTACTEZ-NOUS Column -->
        <div class="w-full md:w-1/3">
            <h3 class="font-bold text-lg md:text-xl mb-3 border-b-2 border-white pb-2">{{ __('messages.contact_us') }}
            </h3>
            <div class="space-y-2">
                <p>Direction de la Pension Civile</p>
                <p>5, Avenue Charles Sumner</p>
                <p>Port-au-Prince, Haïti</p>
            </div>

            @php
                $socialLinks = \Illuminate\Support\Facades\DB::table('parameters')
                    ->whereIn('name', ['social_facebook', 'social_twitter', 'social_linkedin', 'social_youtube'])
                    ->pluck('value', 'name');
                $isSocialUrl = static fn (?string $url) => filled($url) && $url !== '#' && str_starts_with($url, 'http');
            @endphp
            <div class="mt-6">
                <h4 class="font-bold mb-3">{{ __('messages.follow_us') }}</h4>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ $isSocialUrl($socialLinks['social_facebook'] ?? null) ? $socialLinks['social_facebook'] : '#' }}"
                       @if($isSocialUrl($socialLinks['social_facebook'] ?? null)) target="_blank" rel="noopener noreferrer" @endif
                       aria-label="Facebook"
                       class="w-10 h-10 rounded-full border border-white/30 flex items-center justify-center text-white hover:bg-orange-500 hover:border-orange-500 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12.07C22 6.5 17.52 2 12 2S2 6.5 2 12.07C2 17.09 5.66 21.24 10.44 22v-7.01H7.9v-2.92h2.54V9.84c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.92h-2.34V22C18.34 21.24 22 17.09 22 12.07z"/></svg>
                    </a>
                    <a href="{{ $isSocialUrl($socialLinks['social_twitter'] ?? null) ? $socialLinks['social_twitter'] : '#' }}"
                       @if($isSocialUrl($socialLinks['social_twitter'] ?? null)) target="_blank" rel="noopener noreferrer" @endif
                       aria-label="X (Twitter)"
                       class="w-10 h-10 rounded-full border border-white/30 flex items-center justify-center text-white hover:bg-orange-500 hover:border-orange-500 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.24 2H21.5l-7.5 8.57L22.5 22h-6.56l-5.14-6.71L5.2 22H1.92l8.02-9.16L1.5 2h6.72l4.65 6.15L18.24 2zm-1.15 18h1.82L7.04 3.89H5.09L17.09 20z"/></svg>
                    </a>
                    <a href="{{ $isSocialUrl($socialLinks['social_linkedin'] ?? null) ? $socialLinks['social_linkedin'] : '#' }}"
                       @if($isSocialUrl($socialLinks['social_linkedin'] ?? null)) target="_blank" rel="noopener noreferrer" @endif
                       aria-label="LinkedIn"
                       class="w-10 h-10 rounded-full border border-white/30 flex items-center justify-center text-white hover:bg-orange-500 hover:border-orange-500 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.47-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45z"/></svg>
                    </a>
                    <a href="{{ $isSocialUrl($socialLinks['social_youtube'] ?? null) ? $socialLinks['social_youtube'] : '#' }}"
                       @if($isSocialUrl($socialLinks['social_youtube'] ?? null)) target="_blank" rel="noopener noreferrer" @endif
                       aria-label="YouTube"
                       class="w-10 h-10 rounded-full border border-white/30 flex items-center justify-center text-white hover:bg-orange-500 hover:border-orange-500 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.5 6.2a3.02 3.02 0 0 0-2.12-2.14C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.38.46A3.02 3.02 0 0 0 .5 6.2 31.7 31.7 0 0 0 0 12a31.7 31.7 0 0 0 .5 5.8 3.02 3.02 0 0 0 2.12 2.14C4.5 20.4 12 20.4 12 20.4s7.5 0 9.38-.46a3.02 3.02 0 0 0 2.12-2.14A31.7 31.7 0 0 0 24 12a31.7 31.7 0 0 0-.5-5.8zM9.75 15.57V8.43L15.84 12l-6.09 3.57z"/></svg>
                    </a>
                </div>
            </div>

            <div id="newsletter" class="mt-8">
                <h3 class="font-bold text-lg md:text-xl mb-3 border-b-2 border-white pb-2">Newsletter</h3>
                <p class="text-sm text-white/75 mb-3">
                    Recevez les actualités et annonces officielles par e-mail.
                </p>
                <form method="POST" action="{{ route('newsletter.souscription') }}" class="space-y-2">
                    @csrf
                    <input type="email" name="email" required value="{{ old('email') }}"
                           placeholder="votre@email.com"
                           aria-label="Adresse e-mail"
                           class="w-full px-3 py-2.5 rounded-lg bg-white text-gray-800 text-sm placeholder-gray-400 border border-transparent focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <button type="submit"
                            class="w-full px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm rounded-lg transition-colors">
                        S'abonner
                    </button>
                </form>
                @if(session('success'))
                    <p class="text-green-300 text-sm mt-2 flex items-start gap-2">
                        <i class="fas fa-check-circle mt-0.5"></i>
                        <span>{{ session('success') }}</span>
                    </p>
                @endif
                @if(session('error'))
                    <p class="text-red-300 text-sm mt-2 flex items-start gap-2">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                        <span>{{ session('error') }}</span>
                    </p>
                @endif
                @error('email')
                    <p class="text-red-300 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

<!-- Copyright Section -->
<div class="container mx-auto py-6 px-4 sm:px-6 lg:px-8 text-white border-t-2 border-white bg-[#173052] bg-motif-dots">
    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div class="w-full md:w-1/3 text-center md:text-left">
            <p class="text-sm">&copy;{{ date('Y') }} {{ __('messages.direction') }}. Tous droits réservés.</p>
            <p class="text-sm mt-2 text-white/80" aria-label="{{ __('messages.visit_counter') }}">
                <i class="fas fa-eye mr-1 text-orange-400" aria-hidden="true"></i>
                {{ __('messages.visits_total') }} :
                <span class="font-semibold text-white">{{ number_format($totalVisits, 0, ',', ' ') }}</span>
                <span class="mx-1.5 text-white/40">·</span>
                {{ __('messages.visits_today') }} :
                <span class="font-semibold text-white">{{ number_format($todayVisits, 0, ',', ' ') }}</span>
            </p>
        </div>
        <div class="w-full md:w-1/3 text-center">
            <p class="text-sm">        <a href="{{ route('privacy.policy') }}" class="text-sm md:text-base text-white hover:text-orange-500 transition text-center md:text-right">
            Politique de Confidentialité et de la Protection des Données des Utilisateurs
        </a></p>
        </div>
        <div class="w-full md:w-1/3 text-center md:text-right">
            <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                class="mx-auto md:mx-0 md:ml-auto" style="max-width: 80px;">
        </div>
    </div>
</div>

