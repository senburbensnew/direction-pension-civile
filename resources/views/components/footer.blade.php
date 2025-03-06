<!-- Main Footer Section -->
<div class="mx-auto py-6 px-4 sm:px-6 lg:px-8 text-white border-t-4"
    style="background-color: #173152; border-top: 3px solid red;">
    <div class="flex flex-col md:flex-row justify-between items-start md:space-x-6 lg:space-x-8 space-y-8 md:space-y-0">

        <!-- LA DIRECTION Column -->
        <div class="w-full md:w-1/3">
            <h3 class="font-bold text-lg md:text-xl mb-3 border-b-2 border-white pb-2">{{ __('messages.who_are_we') }}
            </h3>
            <ul class="space-y-2">
                <li><a href="#" class="hover:text-orange-500 transition">{{ __('messages.director') }}</a></li>
                <li><a href="#"
                        class="hover:text-orange-500 transition">{{ __('messages.mission_and_responsibilities') }}</a>
                </li>
                <li><a href="#" class="hover:text-orange-500 transition">{{ __('messages.history') }}</a></li>
                <li><a href="#"
                        class="hover:text-orange-500 transition">{{ __('messages.organizational_structure') }}</a></li>
            </ul>
        </div>

        <!-- LIENS UTILES Column -->
        <div class="w-full md:w-1/3">
            <h3 class="font-bold text-lg md:text-xl mb-3 border-b-2 border-white pb-2">{{ __('messages.useful_links') }}
            </h3>
            <ul class="space-y-2">
                <li><a href="https://mef.gouv.ht/" class="hover:text-orange-500 transition">{{ __('messages.MEF') }}</a>
                </li>
                <li><a href="https://budget.gouv.ht/"
                        class="hover:text-orange-500 transition">{{ __('messages.DGB') }}</a></li>
                <li><a href="https://www.douane.gouv.ht/"
                        class="hover:text-orange-500 transition">{{ __('messages.AGD') }}</a></li>
                <li><a href="https://www.brh.ht/" class="hover:text-orange-500 transition">{{ __('messages.BRH') }}</a>
                </li>
                <li><a href="https://bmpad.gouv.ht/"
                        class="hover:text-orange-500 transition">{{ __('messages.BMPAD') }}</a></li>
                <li><a href="https://dgi.gouv.ht/"
                        class="hover:text-orange-500 transition">{{ __('messages.DGI') }}</a></li>
                <li><a href="https://igf.gouv.ht/"
                        class="hover:text-orange-500 transition">{{ __('messages.IGF') }}</a></li>
                <li><a href="https://ihsi.gouv.ht/"
                        class="hover:text-orange-500 transition">{{ __('messages.IHSI') }}</a></li>
                <li><a href="https://oavct.gouv.ht/"
                        class="hover:text-orange-500 transition">{{ __('messages.OAVCT') }}</a></li>
                <li><a href="https://sonapi.gouv.ht/"
                        class="hover:text-orange-500 transition">{{ __('messages.SONAPI') }}</a></li>
                <li><a href="https://www.bnconline.com/"
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

            <h4 class="font-bold mt-6 mb-3">{{ __('messages.follow_us') }}</h4>
            <div class="flex space-x-4 md:space-x-3 justify-start">
                <a href="#" class="text-white hover:text-orange-500 transition text-2xl md:text-base">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" class="text-white hover:text-orange-500 transition text-2xl md:text-base">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="text-white hover:text-orange-500 transition text-2xl md:text-base">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="#" class="text-white hover:text-orange-500 transition text-2xl md:text-base">
                    <i class="fas fa-envelope"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Copyright Section -->
<div class="mx-auto py-6 px-4 sm:px-6 lg:px-8 text-white border-t-2 border-white" style="background-color: #173152;">
    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div class="w-full md:w-1/3 text-center md:text-left">
            <p class="text-sm">&copy;2025 {{ __('messages.direction') }}</p>
        </div>
        <div class="w-full md:w-1/3 text-center">
            <p class="text-sm">{{ __('messages.all_rights_reserved') }}</p>
        </div>
        <div class="w-full md:w-1/3 text-center md:text-right">
            <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                class="mx-auto md:mx-0 md:ml-auto" style="max-width: 80px;">
        </div>
    </div>
</div>
