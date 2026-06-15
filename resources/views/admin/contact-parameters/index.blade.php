@extends('layouts.admin')

@section('title', 'Informations de contact')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Informations de contact</span>
@endsection

@section('content')
<div class="space-y-5" x-data="{
    address:  '{{ addslashes($params['contact_address'] ?? '') }}',
    phone:    '{{ addslashes($params['contact_phone'] ?? '') }}',
    hours:    '{{ addslashes($params['contact_hours'] ?? '') }}',
    email:    '{{ addslashes($params['contact_email'] ?? '') }}',
    facebook: '{{ addslashes($params['social_facebook'] ?? '') }}',
    twitter:  '{{ addslashes($params['social_twitter'] ?? '') }}',
    linkedin: '{{ addslashes($params['social_linkedin'] ?? '') }}',
    youtube:  '{{ addslashes($params['social_youtube'] ?? '') }}',
}">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Informations de contact</h1>
            <p class="text-sm text-gray-500 mt-0.5">Coordonnées et liens affichés sur la page Contact publique.</p>
        </div>
        <a href="{{ route('contact') }}" target="_blank"
           class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 border border-blue-200 hover:border-blue-400 rounded-lg px-3 py-1.5 transition-colors">
            <i class="fas fa-external-link-alt"></i> Voir la page publique
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500 flex-shrink-0"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="flex items-start gap-2 bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0"></i>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.contact-parameters.update') }}">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Left: form --}}
            <div class="xl:col-span-2 space-y-5">

                {{-- Coordonnées --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-address-card text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Coordonnées</p>
                            <p class="text-xs text-gray-400">Adresse, téléphone et e-mail de la DPC</p>
                        </div>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i> Adresse physique <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="contact_address"
                                value="{{ old('contact_address', $params['contact_address'] ?? '') }}" required
                                x-model="address"
                                placeholder="ex. Angle rues Magny et Geffrard, Port-au-Prince"
                                class="w-full border {{ $errors->has('contact_address') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    <i class="fas fa-phone-alt text-gray-400 mr-1"></i> Téléphone <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="contact_phone"
                                    value="{{ old('contact_phone', $params['contact_phone'] ?? '') }}" required
                                    x-model="phone"
                                    placeholder="ex. +(509) 29 92 1007"
                                    class="w-full border {{ $errors->has('contact_phone') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    <i class="fas fa-clock text-gray-400 mr-1"></i> Heures d'ouverture <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="contact_hours"
                                    value="{{ old('contact_hours', $params['contact_hours'] ?? '') }}" required
                                    x-model="hours"
                                    placeholder="ex. Lun–Ven 8h00–16h00"
                                    class="w-full border {{ $errors->has('contact_hours') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                <i class="fas fa-envelope text-gray-400 mr-1"></i> Adresse e-mail <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="contact_email"
                                value="{{ old('contact_email', $params['contact_email'] ?? '') }}" required
                                x-model="email"
                                placeholder="ex. dpc.info@mef.gouv.ht"
                                class="w-full border {{ $errors->has('contact_email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                        </div>
                    </div>
                </div>

                {{-- Google Maps --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-map text-red-500 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Carte Google Maps</p>
                            <p class="text-xs text-gray-400">URL de l'iframe intégré sur la page Contact</p>
                        </div>
                    </div>
                    <div class="p-5">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                            URL <code class="text-xs bg-gray-100 px-1 rounded">src</code> de l'iframe Google Maps
                        </label>
                        <textarea name="contact_map_url" rows="3"
                            placeholder="Collez ici l'URL src de l'iframe fournie par Google Maps (Partager → Intégrer)"
                            class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 resize-none transition font-mono">{{ old('contact_map_url', $params['contact_map_url'] ?? '') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                            <i class="fas fa-info-circle"></i>
                            Dans Google Maps : Partager → Intégrer une carte → copiez uniquement la valeur de l'attribut <code class="bg-gray-100 px-1 rounded">src="..."</code>.
                        </p>
                    </div>
                </div>

                {{-- Social media --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-share-alt text-purple-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Réseaux sociaux</p>
                            <p class="text-xs text-gray-400">Laissez vide pour masquer le réseau</p>
                        </div>
                    </div>
                    <div class="p-5 space-y-4">
                        @php
                            $socialFields = [
                                'social_facebook' => ['label' => 'Facebook',   'icon' => 'fab fa-facebook-f',  'color' => '#1877F2', 'model' => 'facebook'],
                                'social_twitter'  => ['label' => 'X (Twitter)','icon' => 'fab fa-x-twitter',   'color' => '#000000', 'model' => 'twitter'],
                                'social_linkedin' => ['label' => 'LinkedIn',    'icon' => 'fab fa-linkedin-in',  'color' => '#0A66C2', 'model' => 'linkedin'],
                                'social_youtube'  => ['label' => 'YouTube',     'icon' => 'fab fa-youtube',      'color' => '#FF0000', 'model' => 'youtube'],
                            ];
                        @endphp

                        @foreach($socialFields as $key => $field)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-white text-sm"
                                 style="background:{{ $field['color'] }}">
                                <i class="{{ $field['icon'] }}"></i>
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $field['label'] }}</label>
                                <input type="url" name="{{ $key }}"
                                    value="{{ old($key, $params[$key] ?? '') }}"
                                    x-model="{{ $field['model'] }}"
                                    placeholder="https://..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right: live preview --}}
            <div class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden sticky top-4">
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <i class="fas fa-eye text-gray-400 text-sm"></i>
                        <p class="text-sm font-semibold text-gray-700">Aperçu en direct</p>
                    </div>
                    <div class="p-4 space-y-3 text-sm">

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-map-marker-alt text-blue-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Adresse</p>
                                <p class="text-gray-700 text-xs leading-snug mt-0.5" x-text="address || '—'"></p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-phone-alt text-green-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Téléphone</p>
                                <p class="text-gray-700 text-xs mt-0.5" x-text="phone || '—'"></p>
                                <p class="text-gray-400 text-xs" x-text="hours"></p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-envelope text-purple-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">E-mail</p>
                                <p class="text-gray-700 text-xs mt-0.5 break-all" x-text="email || '—'"></p>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-3">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Réseaux</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-if="facebook && facebook !== '#'">
                                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-md text-white" style="background:#1877F2">
                                        <i class="fab fa-facebook-f text-xs"></i> Facebook
                                    </span>
                                </template>
                                <template x-if="twitter && twitter !== '#'">
                                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-md text-white" style="background:#000">
                                        <i class="fab fa-x-twitter text-xs"></i> X
                                    </span>
                                </template>
                                <template x-if="linkedin && linkedin !== '#'">
                                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-md text-white" style="background:#0A66C2">
                                        <i class="fab fa-linkedin-in text-xs"></i> LinkedIn
                                    </span>
                                </template>
                                <template x-if="youtube && youtube !== '#'">
                                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-md text-white" style="background:#FF0000">
                                        <i class="fab fa-youtube text-xs"></i> YouTube
                                    </span>
                                </template>
                                <template x-if="!facebook && !twitter && !linkedin && !youtube">
                                    <span class="text-xs text-gray-400 italic">Aucun réseau configuré</span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 pb-4">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg py-2.5 flex items-center justify-center gap-2 transition-colors">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
