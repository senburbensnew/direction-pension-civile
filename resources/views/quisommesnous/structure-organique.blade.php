@extends('layouts.main')

@section('title', 'Structure organisationnelle')

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

@php
    $servicesDpc = config('dpc_services');
@endphp

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Qui sommes-nous</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Structure organisationnelle</h1>
        </div>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8 space-y-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-sitemap" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Direction et coordination</h2>
            </div>
            <p class="text-gray-700 leading-relaxed">
                La DPC est placée sous la responsabilité d’un cadre ayant un rang de Directeur, assisté par un ou plusieurs autres cadres à titre d’Assistants Directeurs. Le Directeur coordonne l’ensemble des activités de l’entité en question avec l’aide de l’Assistant directeur.
            </p>
            <p class="text-gray-700 leading-relaxed">
                La DPC est divisée en sept (7) services :
            </p>
        </section>

        <section class="bg-white border border-gray-200 card-shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-diagram-project" aria-hidden="true"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-navy">Organigramme</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Organisation des services de la DPC</p>
                    </div>
                </div>
                <a href="{{ asset('images/dpc_organigram.jpg') }}?v=20260918"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="text-xs font-semibold text-navy hover:text-orange-500 inline-flex items-center gap-1.5">
                    Agrandir <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
            <div class="p-4 sm:p-6 bg-white">
                <img src="{{ asset('images/dpc_organigram.jpg') }}?v=20260918"
                     alt="Organigramme de la Direction de la Pension Civile"
                     class="w-full h-auto bg-white">
            </div>
        </section>

        <div class="space-y-5">
            @foreach($servicesDpc as $service)
                <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                                <i class="fa-solid {{ $service['icon'] ?? 'fa-building' }}" aria-hidden="true"></i>
                            </span>
                            <h2 class="text-lg font-bold text-navy">
                                {{ $service['nom'] }}
                            </h2>
                        </div>
                        @if($service['code'])
                            <a href="{{ route('services.show', $service['code']) }}" class="text-sm font-medium text-navy hover:text-orange-500 shrink-0 inline-flex items-center gap-1.5">
                                Voir la page du service <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                    @if(!empty($service['intro']))
                        <p class="text-gray-700 leading-relaxed mb-3">{{ $service['intro'] }}</p>
                    @endif
                    @if(!empty($service['attributions']))
                        <ul class="space-y-2.5 text-gray-700 text-sm leading-relaxed">
                            @foreach($service['attributions'] as $item)
                                <li class="flex items-start gap-2.5">
                                    <i class="fa-solid fa-check text-navy mt-1 text-xs" aria-hidden="true"></i>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
        </div>

    </div>
</div>
@endsection
