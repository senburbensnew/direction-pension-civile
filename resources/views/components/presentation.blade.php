<div class="relative w-full md:w-60 bg-white text-center p-4 border-t border-r border-b">
    <span class="relative -top-8 bg-white px-1 text-lg font-bold text-blue-900">{{ __('messages.director') }}</span>
    <div class="flex justify-center my-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-full md:w-48" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="8" r="5" fill="#888" />
            <path d="M4 20c0-4 4-6 8-6s8 2 8 6" stroke="#888" stroke-width="2" stroke-linecap="round" />
        </svg>
        {{-- <img src="{{ asset('images/presentation.png') }}" alt="directeur" class="w-full md:w-48 rounded-lg"> --}}
    </div>
    <p class="text-base font-bold text-blue-900">Mme Esther Musac</p>
    <p class="text-lg font-extrabold text-blue-900">JEUDY</p>
    <div class="mt-3 text-gray-500">
        <a href="#" class="block hover:underline">{{ __('messages.director_profile') }}</a>
        <hr class="my-2 w-1/2 mx-auto border-gray-300">
        <a href="#" class="block hover:underline">{{ __('messages.speech') }}</a>
    </div>
</div>
