@extends('layouts.admin')

@section('title', 'Paramètres')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Paramètres</span>
@endsection

@section('content')
<div class="space-y-6 w-full">

    <div>
        <h1 class="text-xl font-bold text-gray-800">Paramètres</h1>
        <p class="text-sm text-gray-500 mt-0.5">Configuration générale de l'administration.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('admin.contact-parameters.index') }}"
           class="flex items-start gap-4 bg-white border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition-colors">
                <i class="fas fa-address-card text-blue-600"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">Infos de contact</p>
                <p class="text-xs text-gray-500 mt-0.5">Coordonnées et réseaux sociaux affichés sur la page Contact.</p>
            </div>
        </a>

        <a href="{{ route('admin.rdv-settings.index') }}"
           class="flex items-start gap-4 bg-white border border-gray-200 rounded-lg p-4 hover:border-indigo-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 transition-colors">
                <i class="fas fa-calendar-week text-indigo-600"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">Paramètres RDV</p>
                <p class="text-xs text-gray-500 mt-0.5">Week-end dans les calendriers de réservation et disponibilités agents.</p>
            </div>
        </a>

        <a href="{{ route('admin.email.test') }}"
           class="flex items-start gap-4 bg-white border border-gray-200 rounded-lg p-4 hover:border-emerald-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-100 transition-colors">
                <i class="fas fa-envelope text-emerald-600"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">Test e-mail</p>
                <p class="text-xs text-gray-500 mt-0.5">Vérifier l’envoi SMTP / la configuration mail de l’application.</p>
            </div>
        </a>

        <a href="{{ route('admin.sms.test') }}"
           class="flex items-start gap-4 bg-white border border-gray-200 rounded-lg p-4 hover:border-sky-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center flex-shrink-0 group-hover:bg-sky-100 transition-colors">
                <i class="fas fa-sms text-sky-600"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">Test SMS</p>
                <p class="text-xs text-gray-500 mt-0.5">Vérifier Vonage (Nexmo) ou Twilio selon SMS_DRIVER.</p>
            </div>
        </a>

        <a href="{{ route('admin.queue.index') }}"
           class="flex items-start gap-4 bg-white border border-gray-200 rounded-lg p-4 hover:border-violet-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center flex-shrink-0 group-hover:bg-violet-100 transition-colors">
                <i class="fas fa-layer-group text-violet-600"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">File d’attente</p>
                <p class="text-xs text-gray-500 mt-0.5">Jobs en attente (worker arrêté) et jobs mail/SMS en échec.</p>
            </div>
        </a>

        @php
            $maintenanceOn = \Illuminate\Support\Facades\Cache::remember('is_maintenance_mode', 60, fn() =>
                \Illuminate\Support\Facades\DB::table('parameters')
                    ->where('name', 'is_maintenance_mode')->value('value') === 'true'
            );
        @endphp
        <div class="flex items-start gap-4 bg-white border border-gray-200 rounded-lg p-4">
            <div class="w-10 h-10 rounded-lg {{ $maintenanceOn ? 'bg-yellow-50' : 'bg-gray-50' }} flex items-center justify-center flex-shrink-0">
                <i class="fas fa-tools {{ $maintenanceOn ? 'text-yellow-600' : 'text-gray-400' }}"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">Mode maintenance</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $maintenanceOn ? 'Actuellement activé — le site public est inaccessible.' : 'Actuellement désactivé.' }}
                    Utilisez le bouton dans la barre latérale pour basculer.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
