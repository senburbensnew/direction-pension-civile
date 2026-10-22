@extends('layouts.admin')

@section('title', 'Test SMS')
@section('breadcrumb')
    <a href="{{ route('admin.settings') }}" class="text-gray-500 hover:text-gray-800">Paramètres</a>
    <i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i>
    <span class="text-gray-800">Test SMS</span>
@endsection

@section('content')
    <div class="site-gutter w-full space-y-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-800 mb-1">Test d’envoi SMS</h1>
            <p class="text-sm text-gray-500 max-w-3xl">
                Envoie un SMS réel via le fournisseur configuré
                (<code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $driverLabel }}</code>).
            </p>
        </div>

        @if(!empty($error))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                <p class="font-medium mb-1">Échec de l’envoi</p>
                <p class="text-red-700 break-words">{{ $error }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
            <aside class="xl:col-span-4 space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5 text-sm text-gray-600 space-y-3">
                    <h2 class="text-sm font-semibold text-gray-800">Configuration actuelle</h2>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fournisseur</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">{{ $driverLabel }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Expéditeur</dt>
                            <dd class="mt-0.5 break-all">{{ $from ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">SMS applicatifs</dt>
                            <dd class="mt-0.5">
                                @if($notificationsSmsEnabled)
                                    <span class="text-emerald-700 font-medium">Activés</span>
                                    <span class="text-gray-500">(NOTIFICATIONS_SMS_ENABLED)</span>
                                @else
                                    <span class="text-gray-700">Désactivés</span>
                                    <span class="text-gray-500">— le test admin envoie quand même si le fournisseur est configuré.</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                    @if(! $providerConfigured)
                        <p class="text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 text-xs leading-relaxed">
                            Renseignez les variables d’environnement du fournisseur
                            @if($driver === 'vonage')
                                (<code class="text-[11px]">VONAGE_KEY</code>, <code class="text-[11px]">VONAGE_SECRET</code>, <code class="text-[11px]">VONAGE_SMS_FROM</code>)
                            @else
                                (<code class="text-[11px]">TWILIO_*</code>)
                            @endif
                            puis relancez la page.
                        </p>
                    @endif
                </div>
            </aside>

            <div class="xl:col-span-8">
                <form method="POST" action="{{ route('admin.sms.test.store') }}"
                      class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    @csrf
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Message de test</span>
                    </div>
                    <div class="p-5 sm:p-6 space-y-5">
                        <div>
                            <label for="to" class="block text-sm font-medium text-gray-700 mb-1">Destinataire</label>
                            <input type="tel" id="to" name="to" value="{{ $to }}" required
                                   class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                   placeholder="+50938123456" autocomplete="tel">
                            <p class="text-xs text-gray-500 mt-1">Format international avec indicatif (ex. +509…).</p>
                            <x-input-error :messages="$errors->get('to')" class="mt-1" />
                        </div>
                        <div>
                            <label for="body" class="block text-sm font-medium text-gray-700 mb-1">
                                Message <span class="text-gray-400 font-normal">(optionnel)</span>
                            </label>
                            <textarea id="body" name="body" rows="8"
                                      class="w-full min-h-[160px] px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-y"
                                      placeholder="Laisser vide pour utiliser le texte de test par défaut.">{{ $body }}</textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-1" />
                        </div>
                    </div>
                    <div class="px-5 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/80 flex flex-wrap items-center gap-3">
                        <button type="submit"
                                class="px-5 py-2.5 bg-[#173052] hover:bg-orange-600 text-white text-sm font-semibold rounded-lg">
                            Envoyer le test
                        </button>
                        <a href="{{ route('admin.settings') }}" class="text-sm text-gray-600 hover:text-gray-800">
                            Retour aux paramètres
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
