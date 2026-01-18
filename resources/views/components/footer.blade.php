<!-- Main Footer Section -->
<div class="container mx-auto bg-[#064991] mx-auto py-6 px-4 sm:px-6 lg:px-8 text-white border-t-4"
    style="border-top: 3px solid red;">
    <div class="flex flex-col md:flex-row justify-between items-start md:space-x-6 lg:space-x-8 space-y-8 md:space-y-0">
        <!-- LA DIRECTION Column -->
        <div class="w-full md:w-1/3">
            <h3 class="font-bold text-lg md:text-xl mb-3 border-b-2 border-white pb-2">{{ __('messages.who_are_we') }}
            </h3>
            <ul class="space-y-2">
{{--                 <li><a href="{{ route('quisommesnous.mots') }}"
                        class="hover:text-orange-500 transition">{{ __('messages.director') }}</a></li> --}}
                <li><a href="{{ route('quisommesnous.missions') }}"
                        class="hover:text-orange-500 transition">{{ __('messages.mission_and_responsibilities') }}</a>
                </li>
                <li><a href="{{ route('quisommesnous.historique') }}"
                        class="hover:text-orange-500 transition">{{ __('messages.history') }}</a></li>
                <li><a href="{{ route('quisommesnous.structure-organique') }}"
                        class="hover:text-orange-500 transition">{{ __('messages.organizational_structure') }}</a></li>
            </ul>
        </div>

                <!-- LIENS UTILES Column -->
        <div class="w-full md:w-1/3">
            <h3 class="font-bold text-lg md:text-xl mb-3 border-b-2 border-white pb-2">Directions et Services
            </h3>
            <ul class="space-y-2">
                <li><a href="#" target="_blank"
                        class="hover:text-orange-500 transition">Secrétariat</a>
                </li>
                <li><a href="#" target="_blank"
                        class="hover:text-orange-500 transition">Réception</a></li>
                <li><a href="#" target="_blank"
                        class="hover:text-orange-500 transition">Service Contrôle et Placements (SCP)</a></li>
                <li><a href="#" target="_blank" class="hover:text-orange-500 transition">Cellule Administration (CA)</a>
                </li>
                <li><a href="#" target="_blank"
                        class="hover:text-orange-500 transition">Service Liquidation de Pension (SLP)</a></li>
                <li><a href="#" target="_blank"
                        class="hover:text-orange-500 transition">Service Comptabilité (SC)</a></li>
                <li><a href="#" target="_blank"
                        class="hover:text-orange-500 transition">Service des Formalités (SF)</a></li>
                <li><a href="#"
                        class="hover:text-orange-500 transition">Service Assurance (SA)</a></li>

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
{{--
            <h4 class="font-bold mt-6 mb-3">{{ __('messages.follow_us') }}</h4>
            <div class="flex space-x-4 justify-start text-white text-3xl">
                <a href="#" aria-label="Facebook" class="hover:text-orange-500 transition-colors duration-300">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" aria-label="Twitter" class="hover:text-orange-500 transition-colors duration-300">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" aria-label="YouTube" class="hover:text-orange-500 transition-colors duration-300">
                    <i class="fab fa-youtube"></i>
                </a>
            </div> --}}

        </div>
    </div>
</div>

<!-- Copyright Section -->
<div class="container mx-auto py-6 px-4 sm:px-6 lg:px-8 text-white border-t-2 border-white bg-[#064991]">
    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div class="w-full md:w-1/3 text-center md:text-left">
            <p class="text-sm">&copy;2025 {{ __('messages.direction') }}. Tous droits réservés.</p>
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

