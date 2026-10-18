@extends('layouts.admin')

@section('title', $campaign->exists ? 'Modifier la campagne' : 'Nouvelle campagne')

@section('content')
<x-push-vite :js="['resources/js/admin/newsletter-campaign-editor.js']" />

@php
    $filter = old('audience_filter', $campaign->audience_filter ?? []);
    $groups = config('newsletter.audience_groups', []);
    $topics = config('newsletter.preference_topics', []);
    $locales = config('newsletter.locales', ['fr']);
@endphp

<div class="w-full max-w-6xl mx-auto space-y-6" x-data="{ previewMode: 'desktop' }">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.newsletter.admin.index') }}" class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">{{ $campaign->exists ? 'Modifier la campagne' : 'Nouvelle campagne' }}</h1>
            <p class="text-sm text-gray-500">Audience actuelle : <span class="font-semibold text-blue-600">{{ $recipientCount }}</span> abonné(s) confirmé(s)</p>
        </div>
    </div>

    <form method="POST"
          action="{{ $campaign->exists ? route('admin.newsletter.campaigns.update', $campaign) : route('admin.newsletter.campaigns.store') }}"
          class="grid grid-cols-1 lg:grid-cols-5 gap-5">
        @csrf
        @if($campaign->exists)
            @method('PUT')
        @endif

        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Sujet *</label>
                    <input type="text" name="subject" required value="{{ old('subject', $campaign->subject) }}"
                           class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Texte d’aperçu (preview)</label>
                    <input type="text" name="preview_text" value="{{ old('preview_text', $campaign->preview_text) }}"
                           class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase">Nom expéditeur</label>
                        <input type="text" name="from_name" value="{{ old('from_name', $campaign->from_name) }}"
                               class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase">Reply-to</label>
                        <input type="email" name="reply_to" value="{{ old('reply_to', $campaign->reply_to) }}"
                               class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <label class="text-xs font-semibold text-gray-500 uppercase">Contenu HTML</label>
                <input type="hidden" name="html_body" id="newsletter-html-body" value="{{ old('html_body', $campaign->html_body) }}">
                <textarea name="body" class="hidden">{{ old('body', $campaign->body) }}</textarea>
                <div id="newsletter-html-editor" class="mt-2 bg-white min-h-[220px]"></div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-sm font-semibold text-gray-700">Segmentation</h2>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Langues</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($locales as $loc)
                            <label class="inline-flex items-center gap-1 text-sm">
                                <input type="checkbox" name="audience_filter[locales][]" value="{{ $loc }}"
                                    @checked(in_array($loc, $filter['locales'] ?? [], true))>
                                {{ strtoupper($loc) }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Groupes</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($groups as $key => $label)
                            <label class="inline-flex items-center gap-1 text-sm">
                                <input type="checkbox" name="audience_filter[audience_groups][]" value="{{ $key }}"
                                    @checked(in_array($key, $filter['audience_groups'] ?? [], true))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Préférences (au moins une)</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($topics as $key => $label)
                            <label class="inline-flex items-center gap-1 text-sm">
                                <input type="checkbox" name="audience_filter[preferences_any][]" value="{{ $key }}"
                                    @checked(in_array($key, $filter['preferences_any'] ?? [], true))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 flex flex-wrap gap-4 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="open_tracking_enabled" value="1" @checked(old('open_tracking_enabled', $campaign->open_tracking_enabled))>
                    Suivi d’ouverture (optionnel)
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="click_tracking_enabled" value="1" @checked(old('click_tracking_enabled', $campaign->click_tracking_enabled))>
                    Suivi des clics (optionnel)
                </label>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.newsletter.admin.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-lg">Annuler</a>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" name="action" value="save" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg">Enregistrer brouillon</button>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', optional($campaign->scheduled_at)->format('Y-m-d\TH:i')) }}"
                           class="border border-gray-200 rounded-lg px-2 py-2 text-sm">
                    <button type="submit" name="action" value="schedule" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Planifier</button>
                    <button type="submit" name="action" value="send" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg"
                            onclick="return confirm('Envoyer maintenant à {{ $recipientCount }} destinataire(s) ?')">
                        Envoyer maintenant
                    </button>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4">
            @if($campaign->exists)
                <form method="POST" action="{{ route('admin.newsletter.campaigns.test', $campaign) }}" class="bg-white rounded-xl border border-gray-200 p-4 space-y-2">
                    @csrf
                    <label class="text-xs font-semibold text-gray-500 uppercase">Email de test</label>
                    <input type="email" name="test_email" required placeholder="test@exemple.com" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <button type="submit" class="w-full px-3 py-2 bg-gray-100 hover:bg-gray-200 text-sm rounded-lg">Envoyer un test</button>
                </form>
            @endif

            <div class="sticky top-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-2 border-b flex gap-2 text-xs">
                    <button type="button" @click="previewMode='desktop'" :class="previewMode==='desktop' ? 'text-blue-600 font-semibold' : 'text-gray-400'">Desktop</button>
                    <button type="button" @click="previewMode='mobile'" :class="previewMode==='mobile' ? 'text-blue-600 font-semibold' : 'text-gray-400'">Mobile</button>
                </div>
                <div class="p-4" :class="previewMode === 'mobile' ? 'max-w-[320px] mx-auto' : ''">
                    <div class="bg-[#0f2340] text-white text-center py-3 rounded-lg text-sm font-semibold">Direction de la Pension Civile</div>
                    <p class="mt-3 font-bold text-sm">{{ old('subject', $campaign->subject) ?: '(sujet)' }}</p>
                    <div class="prose prose-sm max-w-none text-gray-600 mt-2">{!! old('html_body', $campaign->html_body) ?: '<p>Contenu…</p>' !!}</div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
