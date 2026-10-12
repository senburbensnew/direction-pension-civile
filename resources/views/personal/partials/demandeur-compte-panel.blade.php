@props([
    'demande',
    'open' => false,
])

@php
    $owner = $demande->relationLoaded('user')
        ? $demande->user
        : ($demande->user ?? \App\Models\User::find($demande->created_by));

    $data = $demande->data ?? [];

    if ($demande->isRencontre()) {
        $nomComplet = $demande->rencontreDemandeurNom();
        $email = $demande->rencontreDemandeurEmail();
    } else {
        $nomComplet = $owner?->displayName()
            ?: trim(
                (string) ($data['firstname'] ?? $data['prenom'] ?? '')
                .' '
                .(string) ($data['lastname'] ?? $data['nom'] ?? '')
            )
            ?: ($data['name'] ?? '—');
        $email = (string) ($owner?->email ?? $data['email'] ?? '—');
    }

    $telephone = $data['telephone'] ?? $owner?->telephone ?? $owner?->phone ?? null;
    $telephoneDisplay = filled($telephone) ? $telephone : '—';
    $telHref = filled($telephone)
        ? 'tel:'.preg_replace('/[^\d+]/', '', (string) $telephone)
        : null;

    $nif = $data['nif'] ?? $owner?->nif ?? null;
    $codePension = $data['pension_code']
        ?? $data['numero_pension']
        ?? $data['code_pension']
        ?? ($owner && $owner->exposesPensionIdentity() ? $owner->displayPensionCode() : null);

    $accountStatus = $owner?->resolvedAccountStatus();
    $statusBadge = match ($accountStatus?->value ?? '') {
        'actif' => 'bg-green-50 text-green-800 ring-green-200',
        'provisoire' => 'bg-amber-50 text-amber-900 ring-amber-200',
        'bloque' => 'bg-red-50 text-red-800 ring-red-200',
        'desactive' => 'bg-gray-100 text-gray-700 ring-gray-200',
        default => 'bg-gray-100 text-gray-600 ring-gray-200',
    };
@endphp

<x-dossier-collapsible
    title="Usager"
    subtitle="Coordonnées et identifiants saisis"
    :open="$open"
>
    <x-slot:actions>
        @if($accountStatus)
            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium ring-1 {{ $statusBadge }}">
                Compte {{ $accountStatus->label() }}
            </span>
        @endif
    </x-slot:actions>

    <dl class="px-5 sm:px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-500">Nom</dt>
            <dd class="mt-1 font-semibold text-gray-900">{{ $nomComplet ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Courriel</dt>
            <dd class="mt-1 font-medium text-gray-800 break-all">
                @if(filled($email) && $email !== '—')
                    <a href="mailto:{{ $email }}" class="text-[#173052] hover:underline">{{ $email }}</a>
                @else
                    —
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Téléphone</dt>
            <dd class="mt-1 font-medium text-gray-800">
                @if($telHref)
                    <a href="{{ $telHref }}" class="inline-flex items-center gap-1.5 text-[#173052] hover:underline">
                        <i class="fas fa-phone-alt text-xs" aria-hidden="true"></i>
                        {{ $telephoneDisplay }}
                    </a>
                @else
                    {{ $telephoneDisplay }}
                @endif
            </dd>
        </div>
        @if(filled($nif))
            <div>
                <dt class="text-xs font-medium text-gray-500">NIF</dt>
                <dd class="mt-1 font-medium text-gray-800 font-mono">{{ $nif }}</dd>
            </div>
        @endif
        @if(filled($codePension))
            <div>
                <dt class="text-xs font-medium text-gray-500">Code / n° pension</dt>
                <dd class="mt-1 font-medium text-gray-800 font-mono">{{ $codePension }}</dd>
            </div>
        @endif
        <div>
            <dt class="text-xs font-medium text-gray-500">Dépôt de la demande</dt>
            <dd class="mt-1 font-medium text-gray-800">
                {{ $demande->created_at->format('d/m/Y à H:i') }}
            </dd>
        </div>
    </dl>

    @role('admin')
        @if($owner)
            <div class="px-5 sm:px-6 py-3 bg-gray-50 border-t border-gray-100">
                <a href="{{ route('admin.users.edit', $owner) }}"
                   class="text-xs font-medium text-[#173052] hover:underline">
                    Fiche utilisateur (administration)
                </a>
            </div>
        @endif
    @endrole
</x-dossier-collapsible>
