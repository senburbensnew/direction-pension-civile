@php
    $avatar = $sexe === 'F'
        ? 'https://ui-avatars.com/api/?name=' . urlencode($nom) . '&background=E91E63&color=fff'
        : 'https://ui-avatars.com/api/?name=' . urlencode($nom) . '&background=0D8ABC&color=fff';

    $photoSrc = str_starts_with($desktopImage, 'http://') || str_starts_with($desktopImage, 'https://')
        ? $desktopImage
        : asset($desktopImage);
@endphp

<div class="relative w-full bg-inherit text-center p-3">
    <span class="text-lg font-bold text-blue-600 block mb-2">
        {{ $role }}
    </span>

    <div class="flex justify-center mb-2">
        <img
            src="{{ $photoSrc }}"
            alt="{{ $nom }}"
            class="w-full h-auto max-h-96 object-contain rounded-lg"
            onerror="this.onerror=null; this.src='{{ $avatar }}';"
        />
    </div>

    <p class="text-base font-bold text-blue-600">
        {{ $nom }}
    </p>

    <div class="mt-3 text-sm text-gray-500 space-y-1">
        @if($showProfileLink && $lienProfil)
            <a href="{{ $lienProfil }}"
               class="block py-1.5 hover:text-blue-600 hover:underline transition-colors">
                {{ __('messages.director_profile') }}
            </a>
        @endif

        @if($showProfileLink && $showSpeechLink && $lienProfil && $lienDiscours)
            <hr class="w-1/2 mx-auto border-gray-300">
        @endif

        @if($showSpeechLink && $lienDiscours)
            <a href="{{ $lienDiscours }}"
               class="block py-1.5 hover:text-blue-600 hover:underline transition-colors">
                {{ __('messages.speech') }}
            </a>
        @endif
    </div>
</div>
