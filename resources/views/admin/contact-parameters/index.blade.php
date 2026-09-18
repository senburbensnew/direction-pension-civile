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
            <p class="text-sm text-gray-500 mt-0.5">Coordonnées, réseaux sociaux et sujets du formulaire de contact.</p>
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

    {{-- Sujets du formulaire --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" x-data="contactSubjectsAdmin()">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-list text-orange-600 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Sujets du formulaire</p>
                    <p class="text-xs text-gray-400">Ajoutez, masquez ou retirez les options du menu Sujet</p>
                </div>
            </div>
            <button type="button" @click="openCreate()"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                <i class="fa-solid fa-plus"></i> Ajouter un sujet
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Libellé</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Ordre</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Sujet libre</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Statut</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subjects as $subject)
                        <tr class="{{ $subject->is_active ? '' : 'bg-gray-50' }}">
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $subject->label }}</td>
                            <td class="px-5 py-3 text-center text-gray-500">{{ $subject->position }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($subject->allows_custom)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-teal-100 text-teal-700">Oui</span>
                                @else
                                    <span class="text-xs text-gray-400">Non</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $subject->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $subject->is_active ? 'Visible' : 'Masqué' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="{{ route('admin.contact-subjects.toggle', $subject) }}" method="POST">
                                        @csrf
                                        <button type="submit" title="{{ $subject->is_active ? 'Masquer' : 'Afficher' }}"
                                            class="px-2 py-1 rounded text-xs font-medium {{ $subject->is_active ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                            <i class="fa-solid {{ $subject->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>
                                    <button type="button" @click="openEdit({{ $subject->toJson() }})"
                                        class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.contact-subjects.destroy', $subject) }}" method="POST"
                                        onsubmit="return confirm('Retirer ce sujet de la liste ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-400">Aucun sujet. Ajoutez-en un pour le formulaire public.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4 z-10">
                <h2 class="text-lg font-bold text-gray-800" x-text="editing ? 'Modifier le sujet' : 'Ajouter un sujet'"></h2>
                <form :action="editing ? '{{ url('/admin/contact-subjects') }}/' + form.id : '{{ route('admin.contact-subjects.store') }}'" method="POST" class="space-y-3">
                    @csrf
                    <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Libellé <span class="text-red-500">*</span></label>
                        <input type="text" name="label" x-model="form.label" required maxlength="150"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ordre d’affichage</label>
                        <input type="number" name="position" x-model="form.position" min="0"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-gray-300 text-blue-600">
                        Visible sur le formulaire public
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="allows_custom" value="0">
                        <input type="checkbox" name="allows_custom" value="1" x-model="form.allows_custom" class="rounded border-gray-300 text-blue-600">
                        Demander un sujet libre (comme « Autre »)
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false" class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function contactSubjectsAdmin() {
        return {
            open: false,
            editing: false,
            form: { id: null, label: '', position: 0, is_active: true, allows_custom: false },
            openCreate() {
                this.editing = false;
                this.form = { id: null, label: '', position: {{ (int) ($subjects->max('position') ?? 0) + 1 }}, is_active: true, allows_custom: false };
                this.open = true;
            },
            openEdit(item) {
                this.editing = true;
                this.form = {
                    id: item.id,
                    label: item.label,
                    position: item.position,
                    is_active: !!item.is_active,
                    allows_custom: !!item.allows_custom,
                };
                this.open = true;
            },
        };
    }
</script>
@endpush

