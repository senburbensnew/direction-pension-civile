@php
    $prefix = $sexe === 'F' ? 'Mme' : 'M.';

    $avatar = $sexe === 'F'
        ? 'https://ui-avatars.com/api/?name=' . urlencode($nom) . '&background=E91E63&color=fff'
        : 'https://ui-avatars.com/api/?name=' . urlencode($nom) . '&background=0D8ABC&color=fff';
@endphp

<div class="relative w-full bg-inherit text-center p-3">
    <span class="text-lg font-bold text-blue-600 block mb-2">
        {{ $role }}
    </span>

    <div class="flex justify-center mb-2">
        <img
            src="{{ asset($desktopImage) }}"
            alt="{{ $nom }}"
            class="w-full h-auto max-h-96 object-contain rounded-lg"
            onerror="this.onerror=null; this.src='{{ $avatar }}';"
        />
    </div>

    <p class="text-base font-bold text-blue-600">
        {{ $nom }}
    </p>

    <div class="mt-2 text-gray-500">
        @if($showProfileLink)
            <a href="{{ $lienProfil }}" class="block hover:underline">
                {{ __('messages.director_profile') }}
            </a>
        @endif

        @if($showProfileLink && $showSpeechLink)
            <hr class="my-1 w-1/2 mx-auto border-gray-300">
        @endif

        @if($showSpeechLink)
            <a href="{{ $lienDiscours }}" class="block hover:underline">
                {{ __('messages.speech') }}
            </a>
        @endif
    </div>
</div>
