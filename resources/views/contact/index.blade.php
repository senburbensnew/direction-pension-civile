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
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1), 0 10px 20px -5px rgba(0, 0, 0, 0.04);
        }
        .contact-card {
            transition: all 0.3s ease;
        }
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
        }
        .icon-container {
            transition: all 0.3s ease;
        }
        .contact-item:hover .icon-container {
            transform: scale(1.1);
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }
        .contact-item:hover .icon-container i {
            color: white;
        }
        .social-icon {
            transition: all 0.3s ease;
        }
        .social-icon:hover {
            transform: translateY(-3px) scale(1.1);
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
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
        }
    </style>

    <!-- Contact Principal -->
    <div class="py-8 bg-transparent">
        <section class="relative fade-in">
            <div class="max-w-7xl mx-auto px-6">
                <div class="bg-white rounded-2xl card-shadow overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                        <!-- Informations de contact -->
                        <div class="p-10 bg-gradient-to-br from-gray-50 to-blue-50">
                            <div class="mb-10">
                                <h2 class="text-3xl font-bold gradient-text mb-2">Connectez-vous avec nous</h2>
                                <p class="text-gray-600">Notre équipe est à votre disposition pour vous accompagner</p>
                            </div>

                            <!-- Contact Items -->
                            <div class="space-y-8">
                                <div class="contact-item flex items-start gap-4">
                                    <div class="icon-container w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-800 mb-1">Notre siège social</h3>
                                        <p class="text-gray-600">{{ $contact['contact_address'] ?? '' }}</p>
                                    </div>
                                </div>

                                <div class="contact-item flex items-start gap-4">
                                    <div class="icon-container w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-phone-alt text-green-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-800 mb-1">Numéro de contact</h3>
                                        <p class="text-gray-600">{{ $contact['contact_phone'] ?? '' }}</p>
                                        <p class="text-gray-600 text-sm mt-1">{{ $contact['contact_hours'] ?? '' }}</p>
                                    </div>
                                </div>

                                <div class="contact-item flex items-start gap-4">
                                    <div class="icon-container w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-envelope text-purple-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-800 mb-1">Adresse e-mail</h3>
                                        <a href="mailto:{{ $contact['contact_email'] ?? '' }}" class="text-blue-600 hover:text-blue-800 transition-colors text-lg font-medium">
                                            {{ $contact['contact_email'] ?? '' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="section-divider"></div>

                            <div class="pt-6">
                                <h3 class="text-xl font-semibold text-gray-800 mb-6">Rejoignez notre communauté</h3>
                                <div class="flex gap-3">
                                    @if(!empty($contact['social_facebook']) && $contact['social_facebook'] !== '#')
                                    <a href="{{ $contact['social_facebook'] }}" class="social-icon w-11 h-11 flex items-center justify-center rounded-full hover:opacity-90 shadow-sm" style="background:#1877F2;" aria-label="Page Facebook">
                                        <i class="fab fa-facebook-f text-base" style="color:white;"></i>
                                    </a>
                                    @endif
                                    @if(!empty($contact['social_twitter']) && $contact['social_twitter'] !== '#')
                                    <a href="{{ $contact['social_twitter'] }}" class="social-icon w-11 h-11 flex items-center justify-center rounded-full hover:opacity-90 shadow-sm" style="background:#000;" aria-label="Profil X (Twitter)">
                                        <i class="fab fa-x-twitter text-base" style="color:white;"></i>
                                    </a>
                                    @endif
                                    @if(!empty($contact['social_linkedin']) && $contact['social_linkedin'] !== '#')
                                    <a href="{{ $contact['social_linkedin'] }}" class="social-icon w-11 h-11 flex items-center justify-center rounded-full hover:opacity-90 shadow-sm" style="background:#0A66C2;" aria-label="Profil LinkedIn">
                                        <i class="fab fa-linkedin-in text-base" style="color:white;"></i>
                                    </a>
                                    @endif
                                    @if(!empty($contact['social_youtube']) && $contact['social_youtube'] !== '#')
                                    <a href="{{ $contact['social_youtube'] }}" class="social-icon w-11 h-11 flex items-center justify-center rounded-full hover:opacity-90 shadow-sm" style="background:#FF0000;" aria-label="Chaîne YouTube">
                                        <i class="fab fa-youtube text-base" style="color:white;"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire de contact -->
                        <div class="p-10">
                            <div class="mb-8">
                                <h2 class="text-3xl font-bold text-gray-800 mb-2">Envoyer un message</h2>
                                <p class="text-gray-600">Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais</p>
                            </div>

                            {{-- Success message --}}
                            @if(session('success'))
                                <div class="mb-6 flex items-start gap-3 bg-green-50 border border-green-300 text-green-800 rounded-lg p-4">
                                    <i class="fas fa-check-circle text-green-500 mt-0.5 flex-shrink-0"></i>
                                    <p class="text-sm">{{ session('success') }}</p>
                                </div>
                            @endif

                            {{-- Validation errors summary --}}
                            @if($errors->any())
                                <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-300 text-red-800 rounded-lg p-4">
                                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0"></i>
                                    <ul class="text-sm list-disc list-inside space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form class="space-y-6" method="POST" action="{{ route('contact.store') }}">
                                @csrf
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">Prénom <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input type="text" id="first_name" name="first_name"
                                                value="{{ old('first_name') }}" required
                                                class="w-full px-4 py-3 border {{ $errors->has('first_name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-user text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Nom <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input type="text" id="last_name" name="last_name"
                                                value="{{ old('last_name') }}" required
                                                class="w-full px-4 py-3 border {{ $errors->has('last_name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-user text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Adresse e-mail <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="email" id="email" name="email"
                                            value="{{ old('email') }}" required
                                            class="w-full px-4 py-3 border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-envelope text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Sujet <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select id="subject" name="subject" required
                                            class="w-full px-4 py-3 border {{ $errors->has('subject') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors appearance-none">
                                            <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Sélectionnez un sujet</option>
                                            <option value="pension"    {{ old('subject') === 'pension'    ? 'selected' : '' }}>Question sur les pensions</option>
                                            <option value="documents"  {{ old('subject') === 'documents'  ? 'selected' : '' }}>Demande de documents</option>
                                            <option value="rendezvous" {{ old('subject') === 'rendezvous' ? 'selected' : '' }}>Prise de rendez-vous</option>
                                            <option value="autre"      {{ old('subject') === 'autre'      ? 'selected' : '' }}>Autre</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Votre message <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <textarea id="message" name="message" rows="5" required
                                            class="w-full px-4 py-3 border {{ $errors->has('message') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg input-focus focus:outline-none transition-colors resize-none">{{ old('message') }}</textarea>
                                        <div class="absolute top-3 right-3 pointer-events-none">
                                            <i class="fas fa-pen text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-800 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-900 transition-all duration-300 flex items-center justify-center gap-2">
                                    <span>Envoyer le message</span>
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- Section Carte -->
        <section class="max-w-7xl mx-auto px-6 pt-16 fade-in">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold gradient-text mb-3">Notre localisation</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Retrouvez-nous facilement à notre siège social situé au cœur de Port-au-Prince</p>
            </div>
            <div class="map-container">
                <iframe
                    src="{{ $contact['contact_map_url'] ?? '' }}"
                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </section>

        <!-- Section Directions Départementales -->
        <section class="max-w-7xl mx-auto px-6 pt-16 fade-in">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold gradient-text mb-3">Nos Directions Départementales</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Retrouvez nos représentations régionales de la Direction de la Pension Civile.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($directions as $dir)
                <div class="bg-white p-6 rounded-xl border border-gray-200 text-center contact-card">
                    <div class="w-12 h-12 bg-{{ $dir->color }}-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-map-pin text-{{ $dir->color }}-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $dir->nom }} ({{ $dir->abbr }})</h3>
                    <p class="text-gray-600">{{ $dir->ville }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Section Services -->
        <section class="max-w-7xl mx-auto px-6 pt-20 fade-in">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold gradient-text mb-3">Nos Services</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Les différents services opérationnels de la DPC / MEF.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($services as $service)
                <div class="bg-white p-6 rounded-xl border border-gray-200 text-center contact-card">
                    <div class="w-12 h-12 bg-{{ $service->color ?? 'blue' }}-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <i class="fas {{ $service->icon ?? 'fa-building' }} text-{{ $service->color ?? 'blue' }}-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $service->nom }}</h3>
                    <p class="text-gray-600">Port-au-Prince</p>
                </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
