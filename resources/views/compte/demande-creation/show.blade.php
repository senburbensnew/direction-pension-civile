<x-app-layout>
    <x-portal-page class="space-y-6">
        <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-indigo-600">
            <p class="dossier-kicker">Compte provisoire</p>
            <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">Ma demande de création de compte</h1>
            <p class="text-sm text-gray-600 mt-2">
                {{ $demande->libellePublic() }}
                · déposée le {{ ($demande->soumise_at ?? $demande->created_at)?->format('d/m/Y à H:i') }}
            </p>
        </header>

        @if (session('success'))
            <div class="text-sm text-green-800 bg-green-50 border border-green-200 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        <section class="dossier-card p-5 sm:p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">Statut de la demande</p>
                    <span class="inline-flex mt-1 text-sm font-semibold px-2.5 py-1 rounded-full {{ $demande->statusBadgeClass() }}">
                        {{ $demande->statusLabel() }}
                    </span>
                </div>
                @if($demande->user)
                    <div class="text-sm text-gray-600 text-right">
                        <p class="text-xs uppercase tracking-wide text-gray-400">Compte associé</p>
                        <p>Utilisateur #{{ $demande->user_id }}</p>
                        <p>Statut : <strong>{{ $demande->user->accountStatusLabel() }}</strong></p>
                    </div>
                @endif
            </div>

            @if($demande->status === \App\Models\DemandeCreationCompte::STATUS_REFUSEE && $demande->refusal_reason)
                <div class="dossier-notice border-l-red-500 bg-red-50 p-4 text-sm text-red-900">
                    <p class="font-semibold">Motif du refus</p>
                    <p class="mt-1">{{ $demande->refusal_reason }}</p>
                </div>
            @endif
        </section>

        @if($editable)
            <form method="post" action="{{ route('compte.demande.update') }}" enctype="multipart/form-data"
                  class="dossier-card p-5 sm:p-6 space-y-4">
                @csrf
                @method('patch')

                <h2 class="text-base font-bold text-gray-800">Corriger ou compléter mes informations</h2>
                <p class="text-sm text-gray-600">Modifications possibles tant que la Direction n’a pas statué sur votre dossier.</p>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prénom</label>
                        <input type="text" name="firstname" value="{{ old('firstname', $demande->firstname) }}"
                               class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @error('firstname')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" name="lastname" value="{{ old('lastname', $demande->lastname) }}"
                               class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @error('lastname')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $demande->email) }}"
                               class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <input type="text" name="telephone" value="{{ old('telephone', $demande->telephone) }}"
                               class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                        @error('telephone')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Adresse</label>
                        <input type="text" name="adresse" value="{{ old('adresse', $demande->adresse) }}"
                               class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                        @error('adresse')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Matricule fiscal (NIF)</label>
                        <input type="text" name="nif" value="{{ old('nif', $demande->nif) }}"
                               class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @error('nif')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">NINU</label>
                        <input type="text" name="ninu" value="{{ old('ninu', $demande->ninu) }}"
                               class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @error('ninu')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code pension</label>
                        <input type="text" name="pension_code" value="{{ old('pension_code', $demande->pension_code) }}"
                               class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @error('pension_code')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4 space-y-3">
                    <p class="text-sm font-medium text-gray-800">Pièce d’identité supplémentaire (optionnel)</p>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600">Type</label>
                            <select name="piece_identite_type" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="">— Ne pas changer —</option>
                                <option value="cin">CIN</option>
                                <option value="passeport">Passeport</option>
                                <option value="permis">Permis</option>
                            </select>
                        </div>
                        <div>
                            <x-file-input
                                name="piece_identite"
                                accept=".jpg,.jpeg,.png,.webp,.pdf"
                                label="Fichier"
                            />
                        </div>
                    </div>
                </div>

                <button type="submit" class="dossier-btn-primary">
                    Enregistrer les modifications
                </button>
            </form>
        @else
            <section class="dossier-card p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-[#173052] mb-4">Informations enregistrées</h2>
                <dl class="grid sm:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">Nom complet</dt><dd class="font-medium">{{ $demande->displayName() }}</dd></div>
                    <div><dt class="text-gray-500">Téléphone</dt><dd class="font-medium">{{ $demande->telephone ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">E-mail</dt><dd class="font-medium">{{ $demande->email ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Adresse</dt><dd class="font-medium">{{ $demande->adresse ?: '—' }}</dd></div>
                </dl>
            </section>
        @endif

        @if($pieces !== [])
            <section class="dossier-card p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-[#173052] mb-3">Documents joints</h2>
                <ul class="text-sm space-y-2">
                    @foreach($pieces as $piece)
                        <li class="flex items-center gap-2 text-gray-700">
                            <span class="text-green-600">✓</span> {{ $piece['label'] }}
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('personal.dashboard') }}" class="dossier-link">Mes demandes</a>
            @if(auth()->user()?->isProvisionnel() && auth()->user()?->hasPensionPortalAccess())
                <a href="{{ route('demandes.rencontre.create') }}" class="dossier-link">Prendre rendez-vous</a>
            @endif
        </div>
    </x-portal-page>
</x-app-layout>
