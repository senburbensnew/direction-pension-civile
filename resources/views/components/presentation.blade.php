@php
    $avatar = $sexe === 'F'
        ? 'https://ui-avatars.com/api/?name=' . urlencode($nom) . '&background=E91E63&color=fff'
        : 'https://ui-avatars.com/api/?name=' . urlencode($nom) . '&background=0D8ABC&color=fff';

    $isCard = $variant === 'card';
@endphp

<div @class([
    'relative w-full text-center',
    'bg-slate-50 border border-gray-200 border-l-0 p-4' => $isCard,
    'bg-transparent p-3' => ! $isCard,
])>
    <span @class([
        'font-bold text-navy block',
        'text-base uppercase tracking-wide mb-3' => $isCard,
        'text-lg mb-2 text-[#033159]' => ! $isCard,
    ])>
        {{ $role }}
    </span>

    <div class="flex justify-center mb-3">
        <x-optimized-image
            :src="$photoComponentSrc"
            :alt="$nom"
            @class([
                'w-full',
                'h-64 object-cover object-top' => $isCard,
                'h-auto max-h-96 object-contain rounded-lg' => ! $isCard,
            ])
            sizes="(min-width: 1024px) 320px, 80vw"
            onerror="this.onerror=null; this.src='{{ $avatar }}';"
        ></x-optimized-image>
    </div>

    <p @class([
        'font-bold text-navy',
        'text-base' => $isCard,
        'text-base text-[#033159]' => ! $isCard,
    ])>
        {{ $nom }}
    </p>

    <div @class([
        'text-[#657786] space-y-0',
        'mt-3 text-base' => $isCard,
        'mt-3 text-base space-y-1' => ! $isCard,
    ])>
        @if($showProfileLink && $lienProfil)
            <a href="{{ $lienProfil }}"
               class="block py-1.5 hover:text-blue-600 hover:underline transition-colors">
                {{ __('messages.director_profile') }}
            </a>
        @endif

        @if($showProfileLink && $showSpeechLink && $lienProfil && $lienDiscours)
            <hr class="w-1/2 border-gray-200">
        @endif

        @if($showSpeechLink && $lienDiscours)
            <a href="{{ $lienDiscours }}"
               class="block py-1.5 hover:text-blue-600 hover:underline transition-colors">
                {{ __('messages.speech') }}
            </a>
        @endif
    </div>
</div>
