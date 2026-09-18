@extends('layouts.main')

@section('title', 'Contact')

@section('content')
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card-shadow {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .contact-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .contact-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 4px; height: 100%;
            background: linear-gradient(to bottom, #3b82f6, #1e40af);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        .contact-card:hover::before { transform: scaleY(1); }
        .contact-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
        }
        .contact-item i,
        .social-icon {
            color: #173052;
            transition: color 0.2s ease;
        }
        .contact-item:hover i,
        .social-icon:hover {
            color: #f97316;
        }
        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            font-size: 1.05rem;
        }
        .form-icon {
            color: #64748b;
            font-size: 0.85rem;
        }
        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #d1d5db, transparent);
            margin: 2rem 0;
        }
        .map-container {
            border-radius: 0;
            overflow: hidden;
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
        }
    </style>

    <!-- Contact Principal -->
    <div class="py-10 bg-transparent">
        <section class="relative fade-in">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold gradient-text mb-3">Contact</h1>
                    <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                        Notre équipe est à votre disposition pour vous accompagner.
                    </p>
                </div>

                <div class="bg-white rounded-none border border-gray-200 card-shadow overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                        <!-- Informations de contact -->
                        <div class="p-8 sm:p-10 bg-gradient-to-br from-gray-50 to-blue-50">
                            <div class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">Nos coordonnées</h2>
                                <p class="text-gray-600 text-sm">Retrouvez-nous facilement et restez en contact</p>
                            </div>

                            <!-- Contact Items -->
                            <div class="space-y-7">
                                <div class="contact-item flex items-start gap-3.5">
                                    <i class="fa-solid fa-fw fa-location-dot text-[15px] mt-1.5 flex-shrink-0" aria-hidden="true"></i>
                                    <div>
                                        <h3 class="text-base font-semibold text-navy mb-0.5">Notre siège social</h3>
                                        <p class="text-gray-600 text-sm leading-relaxed">{{ $contact['contact_address'] ?? '' }}</p>
                                    </div>
                                </div>

                                <div class="contact-item flex items-start gap-3.5">
                                    <i class="fa-solid fa-fw fa-phone text-[15px] mt-1.5 flex-shrink-0" aria-hidden="true"></i>
                                    <div>
                                        <h3 class="text-base font-semibold text-navy mb-0.5">Numéro de contact</h3>
                                        <p class="text-gray-600 text-sm">{{ $contact['contact_phone'] ?? '' }}</p>
                                        <p class="text-gray-500 text-sm mt-0.5">{{ $contact['contact_hours'] ?? '' }}</p>
                                    </div>
                                </div>

                                <div class="contact-item flex items-start gap-3.5">
                                    <i class="fa-solid fa-fw fa-envelope text-[15px] mt-1.5 flex-shrink-0" aria-hidden="true"></i>
                                    <div>
                                        <h3 class="text-base font-semibold text-navy mb-0.5">Adresse e-mail</h3>
                                        <a href="mailto:{{ $contact['contact_email'] ?? '' }}" class="text-sm text-navy hover:text-orange-500 transition-colors font-medium">
                                            {{ $contact['contact_email'] ?? '' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="section-divider"></div>

                            <div class="pt-6">
                                <h3 class="text-xl font-semibold text-gray-800 mb-6">Rejoignez notre communauté</h3>
                                @php
                                    $isSocialUrl = static fn (?string $url) => filled($url) && $url !== '#' && str_starts_with($url, 'http');
                                    $socials = [
                                        ['key' => 'social_facebook', 'icon' => 'fa-facebook-f', 'label' => 'Page Facebook'],
                                        ['key' => 'social_twitter',  'icon' => 'fa-x-twitter',  'label' => 'Profil X (Twitter)'],
                                        ['key' => 'social_linkedin', 'icon' => 'fa-linkedin-in', 'label' => 'Profil LinkedIn'],
                                        ['key' => 'social_youtube',  'icon' => 'fa-youtube',     'label' => 'Chaîne YouTube'],
                                    ];
                                @endphp
                                <div class="flex items-center gap-1">
                                    @foreach($socials as $social)
                                        @php $url = $contact[$social['key']] ?? null; @endphp
                                        <a href="{{ $isSocialUrl($url) ? $url : '#' }}"
                                           class="social-icon"
                                           aria-label="{{ $social['label'] }}"
                                           @if($isSocialUrl($url)) target="_blank" rel="noopener noreferrer" @endif>
                                            <i class="fa-brands {{ $social['icon'] }}" aria-hidden="true"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire de contact -->
                        <div class="p-8 sm:p-10">
                            <div class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">Envoyer un message</h2>
                                <p class="text-gray-600 text-sm">Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais.</p>
                            </div>

                            {{-- Success message --}}
                            @if(session('success'))
                                <div class="mb-6 flex items-start gap-3 bg-green-50 border border-green-300 text-green-800 rounded-lg p-4">
                                    <i class="fa-solid fa-check-circle text-green-500 mt-0.5 flex-shrink-0"></i>
                                    <p class="text-sm">{{ session('success') }}</p>
                                </div>
                            @endif

                            {{-- Validation errors summary --}}
                            @if($errors->any())
                                <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-300 text-red-800 rounded-lg p-4">
                                    <i class="fa-solid fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0"></i>
                                    <ul class="text-sm list-disc list-inside space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form class="space-y-6" method="POST" action="{{ route('contact.store') }}"
                                x-data="{
                                    subject: @js(old('subject', '')),
                                    customSlugs: @js($subjects->where('allows_custom', true)->pluck('slug')->values()),
                                    message: @js(old('message', '')),
                                    maxMessage: {{ \App\Models\Contact::MESSAGE_MAX_LENGTH }}
                                }">
                                @csrf
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">Prénom <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input type="text" id="first_name" name="first_name"
                                                value="{{ old('first_name') }}" required
                                                class="w-full pl-10 pr-4 py-3 border {{ $errors->has('first_name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                                <i class="fa-solid fa-fw fa-user form-icon" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Nom <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input type="text" id="last_name" name="last_name"
                                                value="{{ old('last_name') }}" required
                                                class="w-full pl-10 pr-4 py-3 border {{ $errors->has('last_name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                                <i class="fa-solid fa-fw fa-user form-icon" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Adresse e-mail <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input type="email" id="email" name="email"
                                                value="{{ old('email') }}" required
                                                class="w-full pl-10 pr-4 py-3 border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                                <i class="fa-solid fa-fw fa-envelope form-icon" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input type="tel" id="telephone" name="telephone"
                                                value="{{ old('telephone') }}" required
                                                placeholder="+509XXXXXXXX"
                                                class="w-full pl-10 pr-4 py-3 border {{ $errors->has('telephone') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                                <i class="fa-solid fa-fw fa-phone form-icon" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="destinataire" class="block text-sm font-medium text-gray-700 mb-2">Destinataire <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <select id="destinataire" name="destinataire" required
                                                class="w-full px-4 py-3 pr-10 border {{ $errors->has('destinataire') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors appearance-none">
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
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none">
                                                <i class="fa-solid fa-chevron-down form-icon text-[11px]" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Sujet <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <select id="subject" name="subject" required x-model="subject"
                                                class="w-full px-4 py-3 pr-10 border {{ $errors->has('subject') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors appearance-none">
                                                <option value="" disabled>Sélectionnez un sujet</option>
                                                @foreach($subjects as $item)
                                                    <option value="{{ $item->slug }}">{{ $item->label }}</option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none">
                                                <i class="fa-solid fa-chevron-down form-icon text-[11px]" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                        <div class="mt-4" x-show="customSlugs.includes(subject)" x-cloak>
                                            <label for="custom_subject" class="block text-sm font-medium text-gray-700 mb-2">Précisez votre sujet <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <input type="text" id="custom_subject" name="custom_subject"
                                                    value="{{ old('custom_subject') }}"
                                                    maxlength="150"
                                                    x-bind:required="customSlugs.includes(subject)"
                                                    placeholder="Ex. : demande d’information sur le PRAP"
                                                    class="w-full px-4 py-3 border {{ $errors->has('custom_subject') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label for="message" class="block text-sm font-medium text-gray-700">Votre message <span class="text-red-500">*</span></label>
                                        <span class="text-xs text-gray-500" x-text="`${message.length} / ${maxMessage}`"></span>
                                    </div>
                                    <div class="relative">
                                        <textarea id="message" name="message" rows="5" required
                                            maxlength="{{ \App\Models\Contact::MESSAGE_MAX_LENGTH }}"
                                            x-model="message"
                                            class="w-full px-4 py-3 border {{ $errors->has('message') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors resize-none">{{ old('message') }}</textarea>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Maximum {{ \App\Models\Contact::MESSAGE_MAX_LENGTH }} caractères.</p>
                                </div>

                                <button type="submit"
                                    class="w-full py-3.5 bg-navy hover:bg-orange-500 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <span>Envoyer le message</span>
                                    <i class="fa-solid fa-paper-plane text-sm" aria-hidden="true"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- Section Carte -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 fade-in">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold gradient-text mb-3">Notre localisation</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Retrouvez-nous facilement à notre siège social situé au cœur de Port-au-Prince.</p>
            </div>
            <div class="map-container border border-gray-200">
                <iframe
                    src="{{ $contact['contact_map_url'] ?? '' }}"
                    width="100%" height="420" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </section>

        <!-- Section Directions Départementales -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 fade-in">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold gradient-text mb-3">Nos Directions Départementales</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Retrouvez nos représentations régionales de la Direction de la Pension Civile.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($directions as $dir)
                <a href="{{ route('directions.show', $dir) }}"
                   class="bg-white p-6 rounded-none border border-gray-200 text-center contact-card card-shadow block">
                    <i class="fa-solid fa-fw fa-map-pin text-navy text-lg mb-3" aria-hidden="true"></i>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $dir->nom }} ({{ $dir->abbr }})</h3>
                    <p class="text-gray-600 text-sm">{{ $dir->ville }}</p>
                </a>
                @endforeach
            </div>
        </section>

        <!-- Section Services -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-4 fade-in">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold gradient-text mb-3">Nos Services</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Les différents services opérationnels de la DPC / MEF.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($services as $service)
                <a href="{{ route('services.show', $service) }}"
                   class="bg-white p-6 rounded-none border border-gray-200 text-center contact-card card-shadow block">
                    <i class="fa-solid fa-fw {{ $service->icon ?? 'fa-building' }} text-navy text-lg mb-3" aria-hidden="true"></i>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $service->nom }}</h3>
                    <p class="text-gray-600 text-sm">Port-au-Prince</p>
                </a>
                @endforeach
            </div>
        </section>
    </div>
@endsection
