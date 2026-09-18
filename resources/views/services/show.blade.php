@extends('layouts.main')

@section('title', $service->nom)

@section('content')
<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <nav class="text-sm text-gray-500 mb-8">
            <a href="{{ route('home') }}" class="hover:text-navy">Accueil</a>
            <span class="mx-2">/</span>
            <a href="{{ route('contact') }}" class="hover:text-navy">Contact</a>
            <span class="mx-2">/</span>
            <span class="text-navy font-medium">{{ $service->nom }}</span>
        </nav>

        <div class="bg-white rounded-none border border-gray-200 shadow-sm p-8 md:p-10">
            <div class="flex items-start gap-4 mb-6">
                <i class="fa-solid fa-fw {{ $service->icon ?: 'fa-building' }} text-navy text-xl mt-1" aria-hidden="true"></i>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Service</p>
                    <h1 class="text-3xl font-bold text-navy">{{ $service->nom }}</h1>
                    <p class="text-gray-600 mt-2">Siège · Port-au-Prince</p>
                </div>
            </div>

            <p class="text-gray-700 leading-relaxed text-[15px]">
                {{ $service->description ?: "Service opérationnel de la Direction de la Pension Civile." }}
            </p>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Localisation</p>
                    <p class="font-medium text-navy">Port-au-Prince</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Contact</p>
                    <a href="{{ route('contact') }}" class="font-medium text-navy hover:text-orange-500">Écrire à la DPC</a>
                </div>
            </div>
        </div>

        @if($others->isNotEmpty())
        <section class="mt-12">
            <h2 class="text-xl font-bold text-navy mb-5">Autres services</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($others as $other)
                <a href="{{ route('services.show', $other) }}"
                   class="bg-white rounded-none border border-gray-200 p-5 hover:border-navy/40 transition-colors">
                    <i class="fa-solid fa-fw {{ $other->icon ?: 'fa-building' }} text-navy mb-2" aria-hidden="true"></i>
                    <h3 class="font-semibold text-gray-800">{{ $other->nom }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Port-au-Prince</p>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>
@endsection
