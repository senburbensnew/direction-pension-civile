@extends('layouts.admin')

@section('title', 'Test e-mail')
@section('breadcrumb')
    <a href="{{ route('admin.settings') }}" class="text-gray-500 hover:text-gray-800">Paramètres</a>
    <i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i>
    <span class="text-gray-800">Test e-mail</span>
@endsection

@section('content')
    <div class="site-gutter w-full space-y-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-800 mb-1">Test d’envoi d’e-mail</h1>
            <p class="text-sm text-gray-500 max-w-3xl">
                Envoie un message réel via la configuration Laravel actuelle. Utile pour valider SMTP ou le driver
                <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $mailer }}</code>.
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
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Driver</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">{{ $mailer }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Expéditeur</dt>
                            <dd class="mt-0.5 break-all">
                                {{ $fromName ? "{$fromName} " : '' }}&lt;{{ $fromAddress ?? '—' }}&gt;
                            </dd>
                        </div>
                        @if($mailer === 'smtp' && $smtpHost)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Serveur SMTP</dt>
                                <dd class="mt-0.5">{{ $smtpHost }}</dd>
                            </div>
                        @endif
                    </dl>
                    @if($mailer === 'log')
                        <p class="text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 text-xs leading-relaxed">
                            Le driver <strong>log</strong> n’envoie pas de vrai e-mail : consultez
                            <code class="text-[11px]">storage/logs/laravel.log</code>.
                        </p>
                    @endif
                </div>
            </aside>

            <div class="xl:col-span-8">
                <form method="POST" action="{{ route('admin.email.test.store') }}"
                      class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Message de test</span>
                    </div>
                    <div class="p-5 sm:p-6 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label for="to" class="block text-sm font-medium text-gray-700 mb-1">Destinataire</label>
                                <input type="email" id="to" name="to" value="{{ $to }}" required
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                       placeholder="exemple@domaine.com">
                                <x-input-error :messages="$errors->get('to')" class="mt-1" />
                            </div>
                            <div class="md:col-span-2">
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Objet</label>
                                <input type="text" id="subject" name="subject" value="{{ $subject }}" required
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                            </div>
                            <div class="md:col-span-2">
                                <label for="body" class="block text-sm font-medium text-gray-700 mb-1">
                                    Message <span class="text-gray-400 font-normal">(optionnel)</span>
                                </label>
                                <textarea id="body" name="body" rows="14"
                                          class="w-full min-h-[280px] px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-y"
                                          placeholder="Laisser vide pour utiliser le texte de test par défaut.">{{ $body }}</textarea>
                                <x-input-error :messages="$errors->get('body')" class="mt-1" />
                            </div>
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
