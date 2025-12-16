@php
    // Prefix based on gender
    $prefix = $sexe === 'F' ? 'Mme' : 'M.';

    // Dynamic fallback avatar color based on sexe
    $avatar = $sexe === 'F'
        ? 'https://ui-avatars.com/api/?name=' . urlencode($nom) . '&background=E91E63&color=fff'
        : 'https://ui-avatars.com/api/?name=' . urlencode($nom) . '&background=0D8ABC&color=fff';
@endphp

<div class="relative w-full md:w-60 bg-gray-50 text-center p-3 border-t border-r border-b border-blue-600">

    <!-- ROLE -->
    <span class="relative -top-8 bg-gray-50 px-1 text-lg font-bold text-blue-600">
        {{ $role }}
    </span>

    <!-- IMAGES -->
    <div class="flex justify-center mb-2">
        <!-- MOBILE -->
        <img
            src="{{ asset($mobileImage) }}"
            alt="{{ $nom }}"
            class="block md:hidden w-full h-auto max-h-64 object-contain rounded-lg"
            onerror="this.onerror=null; this.src='{{ $avatar }}';"
        />

<!-- DESKTOP -->
<img
    src="{{ asset($desktopImage) }}"
    alt="{{ $nom }}"
    class="hidden md:block w-full h-auto max-h-72 object-contain rounded-lg"
    onerror="this.onerror=null; this.src='{{ $avatar }}';"
/>

    </div>

    <!-- FULL NAME WITH PREFIX -->
    <p class="text-base font-bold text-blue-600">
        {{ $prefix }} {{ $nom }}
    </p>

    <!-- LINKS -->
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
