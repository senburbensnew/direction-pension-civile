@extends('layouts.main')

@section('title', 'Liens Utiles')

@section('content')
    <!-- Main Template -->
    <div class="w-full bg-blue-900 text-white py-3 md:py-4 text-center font-bold text-xl md:text-2xl"
        style="background-color: #074482;">
        Liens Utiles
    </div>

    <div class="mx-auto p-4 md:p-6 max-w-2xl">
        <ul class="text-[#5156be]">
            @foreach ($links as $link)
                <li class="border-b border-gray-200 pb-2 md:pb-3 last:border-b-0 last:pb-0">
                    <x-tooltip :text="$link['abbr']">
                        <a href="{{ $link['link'] }}" class="py-2 px-2 md:px-4 inline-block text-sm md:text-base">
                            {{ $link['name'] }}, <span class="font-bold">({{ $link['abbr'] }})</span>
                        </a>
                    </x-tooltip>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
