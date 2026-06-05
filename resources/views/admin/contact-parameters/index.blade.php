@extends('layouts.admin')

@section('title', 'Informations de contact')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Informations de contact</span>
@endsection

@section('content')
<div class="max-w-2xl space-y-6">

    <div>
        <h1 class="text-xl font-bold text-gray-800">Informations de contact</h1>
        <p class="text-sm text-gray-500 mt-0.5">Adresse, téléphone, e-mail et liens sociaux affichés sur la page Contact.</p>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.contact-parameters.update') }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
        @csrf @method('PUT')

        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide border-b border-gray-100 pb-2">Coordonnées</h2>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Adresse *</label>
            <input type="text" name="contact_address" value="{{ old('contact_address', $params['contact_address'] ?? '') }}" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Téléphone *</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone', $params['contact_phone'] ?? '') }}" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Heures d'ouverture *</label>
                <input type="text" name="contact_hours" value="{{ old('contact_hours', $params['contact_hours'] ?? '') }}" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Adresse e-mail *</label>
            <input type="email" name="contact_email" value="{{ old('contact_email', $params['contact_email'] ?? '') }}" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">URL Google Maps (iframe src)</label>
            <textarea name="contact_map_url" rows="2"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('contact_map_url', $params['contact_map_url'] ?? '') }}</textarea>
        </div>

        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide border-b border-gray-100 pb-2 pt-2">Réseaux sociaux</h2>

        @foreach(['social_facebook' => ['Facebook', 'fab fa-facebook-f'], 'social_twitter' => ['X (Twitter)', 'fab fa-x-twitter'], 'social_linkedin' => ['LinkedIn', 'fab fa-linkedin-in'], 'social_youtube' => ['YouTube', 'fab fa-youtube']] as $key => [$label, $icon])
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                <i class="{{ $icon }} mr-1"></i>{{ $label }}
            </label>
            <input type="url" name="{{ $key }}" value="{{ old($key, $params[$key] ?? '') }}" placeholder="https://..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        @endforeach

        <div class="flex justify-end pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-save mr-1.5"></i> Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
