<div class="relative w-full md:w-60 bg-white text-center p-4
            border-t border-r border-b border-blue-900">

    <span class="relative -top-8 bg-white px-1 text-lg font-bold text-blue-900">
        {{ __('messages.director') }}
    </span>

    <div class="flex justify-center my-3">
        <img
            src="{{ asset('images/presentation2.PNG') }}"
            alt="directeur"
            class="w-full md:w-48 rounded-lg"
            onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Esther+Musac&background=0D8ABC&color=fff';"
        >
    </div>

    <p class="text-base font-bold text-blue-900">Mme Esther Musac</p>
    <p class="text-lg font-extrabold text-blue-900">JEUDY</p>

    <div class="mt-3 text-gray-500">
        <a href="#" class="block hover:underline">{{ __('messages.director_profile') }}</a>
        <hr class="my-2 w-1/2 mx-auto border-gray-300">
        <a href="#" class="block hover:underline">{{ __('messages.speech') }}</a>
    </div>
</div>
