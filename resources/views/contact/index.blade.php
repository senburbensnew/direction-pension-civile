@extends('layouts.main')

@section('title', 'Contact')

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    .social-icon:hover { color: #f97316; }
    [x-cloak] { display: none !important; }
</style>

@php
    $inputClass = fn (string $field) => 'w-full px-4 py-2.5 border text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy '
        . ($errors->has($field) ? 'border-red-400 bg-red-50' : 'border-gray-200');
    $iconInputClass = fn (string $field) => 'w-full pl-10 pr-4 py-2.5 border text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy '
        . ($errors->has($field) ? 'border-red-400 bg-red-50' : 'border-gray-200');
    $isSocialUrl = static fn (?string $url) => filled($url) && $url !== '#' && str_starts_with($url, 'http');
    $socials = [
        ['key' => 'social_facebook', 'icon' => 'fa-facebook-f', 'label' => 'Page Facebook'],
        ['key' => 'social_twitter',  'icon' => 'fa-x-twitter',  'label' => 'Profil X (Twitter)'],
        ['key' => 'social_linkedin', 'icon' => 'fa-linkedin-in', 'label' => 'Profil LinkedIn'],
        ['key' => 'social_youtube',  'icon' => 'fa-youtube',     'label' => 'Chaîne YouTube'],
    ];
@endphp

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Nous joindre</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Contact</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Notre équipe est à votre disposition pour vous accompagner.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-address-book" aria-hidden="true"></i>
                    </span>
                    <h2 class="text-xl font-bold text-navy">Nos coordonnées</h2>
                </div>
                <p class="text-gray-600 text-sm mb-6">Retrouvez-nous facilement et restez en contact</p>

                <div class="space-y-6">
                    <div class="flex items-start gap-3">
                        <span class="text-navy mt-0.5 shrink-0"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                        <div>
                            <h3 class="text-base font-semibold text-navy mb-0.5">Notre siège social</h3>
                            <p class="text-gray-700 text-sm leading-relaxed">{{ $contact['contact_address'] ?? '' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-navy mt-0.5 shrink-0"><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
                        <div>
                            <h3 class="text-base font-semibold text-navy mb-0.5">Numéro de contact</h3>
                            <p class="text-gray-700 text-sm">{{ $contact['contact_phone'] ?? '' }}</p>
                            <p class="text-gray-500 text-sm mt-0.5">{{ $contact['contact_hours'] ?? '' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-navy mt-0.5 shrink-0"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                        <div>
                            <h3 class="text-base font-semibold text-navy mb-0.5">Adresse e-mail</h3>
                            <a href="mailto:{{ $contact['contact_email'] ?? '' }}" class="text-sm text-navy hover:text-orange-500 font-medium">
                                {{ $contact['contact_email'] ?? '' }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-bold text-navy mb-4">Rejoignez notre communauté</h3>
                    <div class="flex items-center gap-3">
                        @foreach($socials as $social)
                            @php $url = $contact[$social['key']] ?? null; @endphp
                            <a href="{{ $isSocialUrl($url) ? $url : '#' }}"
                               class="social-icon text-navy text-lg"
                               aria-label="{{ $social['label'] }}"
                               @if($isSocialUrl($url)) target="_blank" rel="noopener noreferrer" @endif>
                                <i class="fa-brands {{ $social['icon'] }}" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    </span>
                    <h2 class="text-xl font-bold text-navy">Envoyer un message</h2>
                </div>
                <p class="text-gray-600 text-sm mb-6">Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais.</p>

                @if(session('success'))
                    <div class="mb-6 flex items-start gap-3 bg-green-50 border border-green-300 text-green-800 p-4">
                        <i class="fa-solid fa-check-circle text-green-500 mt-0.5 flex-shrink-0"></i>
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-300 text-red-800 p-4">
                        <i class="fa-solid fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0"></i>
                        <ul class="text-sm list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="space-y-5" method="POST" action="{{ route('contact.store') }}"
                    x-data="{
                        subject: @js(old('subject', '')),
                        customSlugs: @js($subjects->where('allows_custom', true)->pluck('slug')->values()),
                        message: @js(old('message', '')),
                        maxMessage: {{ \App\Models\Contact::MESSAGE_MAX_LENGTH }}
                    }">
                    @csrf
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">Prénom <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" id="first_name" name="first_name"
                                    value="{{ old('first_name') }}" required
                                    class="{{ $iconInputClass('first_name') }}">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-user text-sm" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Nom <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" id="last_name" name="last_name"
                                    value="{{ old('last_name') }}" required
                                    class="{{ $iconInputClass('last_name') }}">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-user text-sm" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Adresse e-mail <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="email" id="email" name="email"
                                    value="{{ old('email') }}" required
                                    class="{{ $iconInputClass('email') }}">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-envelope text-sm" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="tel" id="telephone" name="telephone"
                                    value="{{ old('telephone') }}" required
                                    placeholder="+509XXXXXXXX"
                                    class="{{ $iconInputClass('telephone') }}">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-phone text-sm" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="destinataire" class="block text-sm font-medium text-gray-700 mb-2">Destinataire <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="destinataire" name="destinataire" required
                                    class="{{ $inputClass('destinataire') }} pr-10 appearance-none">
                                    <option value="" disabled {{ old('destinataire') ? '' : 'selected' }}>Sélectionnez un destinataire</option>
                                    <optgroup label="Services">
                                        @foreach($services as $service)
                                            <option value="{{ \App\Models\Contact::destinataireKey('service', $service->code) }}"
                                                {{ old('destinataire') === \App\Models\Contact::destinataireKey('service', $service->code) ? 'selected' : '' }}>
                                                {{ $service->nom }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Directions départementales">
                                        @foreach($directions as $dir)
                                            <option value="{{ \App\Models\Contact::destinataireKey('direction', $dir->abbr) }}"
                                                {{ old('destinataire') === \App\Models\Contact::destinataireKey('direction', $dir->abbr) ? 'selected' : '' }}>
                                                {{ $dir->nom }} ({{ $dir->abbr }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-chevron-down text-[11px]" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Sujet <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="subject" name="subject" required x-model="subject"
                                    class="{{ $inputClass('subject') }} pr-10 appearance-none">
                                    <option value="" disabled>Sélectionnez un sujet</option>
                                    @foreach($subjects as $item)
                                        <option value="{{ $item->slug }}">{{ $item->label }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-chevron-down text-[11px]" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="mt-4" x-show="customSlugs.includes(subject)" x-cloak>
                                <label for="custom_subject" class="block text-sm font-medium text-gray-700 mb-2">Précisez votre sujet <span class="text-red-500">*</span></label>
                                <input type="text" id="custom_subject" name="custom_subject"
                                    value="{{ old('custom_subject') }}"
                                    maxlength="150"
                                    x-bind:required="customSlugs.includes(subject)"
                                    placeholder="Ex. : demande d’information sur le PRAP"
                                    class="{{ $inputClass('custom_subject') }}">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="message" class="block text-sm font-medium text-gray-700">Votre message <span class="text-red-500">*</span></label>
                            <span class="text-xs text-gray-500" x-text="`${message.length} / ${maxMessage}`"></span>
                        </div>
                        <textarea id="message" name="message" rows="5" required
                            maxlength="{{ \App\Models\Contact::MESSAGE_MAX_LENGTH }}"
                            x-model="message"
                            class="{{ $inputClass('message') }} resize-none">{{ old('message') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Maximum {{ \App\Models\Contact::MESSAGE_MAX_LENGTH }} caractères.</p>
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-navy text-white font-semibold hover:opacity-90 transition flex items-center justify-center gap-2">
                        <span>Envoyer le message</span>
                        <i class="fa-solid fa-paper-plane text-sm" aria-hidden="true"></i>
                    </button>
                </form>
            </section>
        </div>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Notre localisation</h2>
            </div>
            <p class="text-gray-600 text-sm mb-6">Retrouvez-nous facilement à notre siège social situé au cœur de Port-au-Prince.</p>
            <div class="border border-gray-200 overflow-hidden">
                <iframe
                    src="{{ $contact['contact_map_url'] ?? '' }}"
                    width="100%" height="420" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </section>

        <section>
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Nos Directions Départementales</h2>
            </div>
            <p class="text-gray-600 text-sm mb-5">Retrouvez nos représentations régionales de la Direction de la Pension Civile.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($directions as $dir)
                    <a href="{{ route('directions.show', $dir) }}"
                       class="bg-white p-6 border border-gray-200 card-shadow block hover:border-navy/30 transition">
                        <div class="flex items-start gap-3">
                            <span class="text-xl text-navy mt-0.5 shrink-0"><i class="fa-solid fa-map-pin" aria-hidden="true"></i></span>
                            <div>
                                <h3 class="text-lg font-bold text-navy mb-1">{{ $dir->nom }} ({{ $dir->abbr }})</h3>
                                <p class="text-gray-700 text-sm">{{ $dir->ville }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <section>
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-sitemap" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Nos Services</h2>
            </div>
            <p class="text-gray-600 text-sm mb-5">Les différents services opérationnels de la DPC / MEF.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($services as $service)
                    <a href="{{ route('services.show', $service) }}"
                       class="bg-white p-6 border border-gray-200 card-shadow block hover:border-navy/30 transition">
                        <div class="flex items-start gap-3">
                            <span class="text-xl text-navy mt-0.5 shrink-0"><i class="fa-solid {{ $service->icon ?? 'fa-building' }}" aria-hidden="true"></i></span>
                            <div>
                                <h3 class="text-lg font-bold text-navy mb-1">{{ $service->nom }}</h3>
                                <p class="text-gray-700 text-sm">Port-au-Prince</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

    </div>
</div>
@endsection
