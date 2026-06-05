@extends('layouts.admin')

@section('title', 'Rédiger une newsletter')

@section('content')
<div class="max-w-4xl" x-data="{ subject: '', body: '', tab: 'editor' }">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.newsletter.admin.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Rédiger une newsletter</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Sera envoyée à <span class="font-semibold text-blue-600">{{ $total }}</span> abonné{{ $total > 1 ? 's' : '' }}
            </p>
        </div>
    </div>

    <form action="{{ route('admin.newsletter.send') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            {{-- ── Éditeur ─────────────────────────────────────────── --}}
            <div class="lg:col-span-3 space-y-4">

                {{-- Sujet --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Sujet <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject" x-model="subject" required
                           value="{{ old('subject') }}"
                           placeholder="Objet de la newsletter…"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    @error('subject')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Corps --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Contenu <span class="text-red-500">*</span>
                        </label>
                        <div class="flex rounded-lg border border-gray-200 overflow-hidden text-xs">
                            <button type="button" @click="tab = 'editor'"
                                    :class="tab === 'editor' ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
                                    class="px-3 py-1.5 transition-colors font-medium">
                                Rédiger
                            </button>
                            <button type="button" @click="tab = 'preview'"
                                    :class="tab === 'preview' ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
                                    class="px-3 py-1.5 transition-colors font-medium border-l border-gray-200">
                                Aperçu
                            </button>
                        </div>
                    </div>

                    <div x-show="tab === 'editor'">
                        <textarea name="body" x-model="body" required rows="14"
                                  placeholder="Rédigez ici le contenu de votre newsletter…"
                                  class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none leading-relaxed">{{ old('body') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1.5">
                            <i class="fas fa-info-circle mr-1"></i>
                            Les sauts de ligne sont conservés dans l'email.
                        </p>
                    </div>

                    <div x-show="tab === 'preview'" x-cloak
                         class="border border-gray-200 rounded-lg p-4 min-h-[200px] prose prose-sm max-w-none text-gray-700 leading-relaxed text-sm whitespace-pre-wrap"
                         x-text="body || 'Le contenu apparaîtra ici…'">
                    </div>

                    @error('body')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.newsletter.admin.index') }}"
                       class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Annuler
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            onclick="return confirm('Envoyer cette newsletter à {{ $total }} abonné{{ $total > 1 ? 's' : '' }} ?')">
                        <i class="fas fa-paper-plane text-xs"></i>
                        Envoyer à {{ $total }} abonné{{ $total > 1 ? 's' : '' }}
                    </button>
                </div>
            </div>

            {{-- ── Aperçu email ────────────────────────────────────── --}}
            <div class="lg:col-span-2">
                <div class="sticky top-6">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                        <i class="fas fa-eye mr-1"></i> Aperçu email
                    </p>

                    <div class="rounded-xl border border-gray-200 shadow-sm overflow-hidden text-sm">
                        {{-- Email header --}}
                        <div class="px-4 py-3 text-xs text-gray-500 bg-gray-50 border-b border-gray-200 space-y-1">
                            <p><span class="font-medium text-gray-700">De :</span> Direction de la Pension Civile</p>
                            <p><span class="font-medium text-gray-700">À :</span> abonné@exemple.com</p>
                            <p class="truncate">
                                <span class="font-medium text-gray-700">Sujet :</span>
                                <span x-text="subject || '(sans sujet)'"></span>
                            </p>
                        </div>

                        {{-- Email body preview --}}
                        <div class="bg-white p-4">
                            <div class="bg-[#0f2340] rounded-lg px-4 py-3 text-center mb-3">
                                <p class="text-white text-sm font-semibold">Direction de la Pension Civile</p>
                                <p class="text-blue-300 text-xs">Newsletter officielle</p>
                            </div>

                            <p class="font-bold text-gray-800 text-sm mb-2" x-text="subject || '(sujet)'"></p>
                            <p class="text-gray-600 text-xs leading-relaxed whitespace-pre-wrap line-clamp-6"
                               x-text="body || 'Le contenu de la newsletter apparaîtra ici…'"></p>

                            <div class="mt-3 pt-3 border-t border-gray-100 text-center">
                                <p class="text-gray-400 text-[10px]">
                                    Vous recevez cet email car vous êtes abonné.<br>
                                    <span class="text-blue-400 underline cursor-pointer">Se désabonner</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg px-3 py-3 text-xs text-amber-800">
                        <p class="font-semibold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Avant d'envoyer</p>
                        <ul class="space-y-1 text-amber-700">
                            <li>· Vérifiez l'objet et le contenu</li>
                            <li>· L'envoi est immédiat et irréversible</li>
                            <li>· Chaque abonné recevra un lien de désabonnement</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
