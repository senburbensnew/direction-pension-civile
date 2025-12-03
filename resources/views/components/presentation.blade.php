<!-- Director Card -->
<div class="relative w-full md:w-60 bg-gray-50 text-center p-4 border-t border-r border-b border-blue-600">
    <span class="relative -top-8 bg-gray-50 px-1 text-lg font-bold text-blue-600">
        {{ __('messages.director') }}
    </span>

    <div class="flex justify-center mb-2">
        <!-- MOBILE: Landscape -->
        <img
            src="{{ asset('images/directrice-landscape.jpg') }}"
            alt="directeur"
            class="block md:hidden w-full h-auto max-h-64 object-contain rounded-lg"
            onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Esther+Musac&background=0D8ABC&color=fff';"
        />

        <!-- DESKTOP: Portrait -->
        <img
            src="{{ asset('images/directrice.jpg') }}"
            alt="directeur"
            class="hidden md:block md:w-48 md:h-72 object-cover rounded-lg"
            onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Esther+Musac&background=0D8ABC&color=fff';"
        />
    </div>

    <p class="text-base font-bold text-blue-600">Mme Esther Musac</p>
    <p class="text-lg font-extrabold text-blue-600">JEUDY</p>

    <div class="mt-2 text-gray-500">
        <a href="#" class="block hover:underline">{{ __('messages.director_profile') }}</a>
        <hr class="my-1 w-1/2 mx-auto border-gray-300">
        <a href="#" class="block hover:underline">{{ __('messages.speech') }}</a>
    </div>
</div>
