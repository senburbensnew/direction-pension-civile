<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $demande->code }}</title>
    @include('demandes.pdf._styles')
</head>
<body>

<div class="confidential">Direction des Pensions Civiles</div>

<div class="page">

    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <p class="republic">République d'Haïti — Ministère de l'Économie et des Finances</p>
                <p class="institution">Direction des Pensions Civiles</p>
                <p class="subtitle">Gestion des dossiers de pension du secteur public</p>
            </div>
            <div class="header-right">
                <span class="doc-type-badge">Demande d'adhésion</span>
                <div class="ref-block">
                    <strong>Référence :</strong> {{ $demande->code }}<br>
                    <strong>Émis le :</strong> {{ now()->format('d/m/Y') }}<br>
                    @if($demande->service)
                        <strong>Service :</strong> {{ $demande->service->nom }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="status-bar">
        <div>
            <div class="status-label">Statut du dossier</div>
            <div class="status-value">{{ $demande->status->label ?? $demande->status->code }}</div>
        </div>
        <div class="date-info">
            Créé le {{ $demande->created_at->format('d/m/Y') }}<br>
            @if($demande->submitted_at)
                Soumis le {{ $demande->submitted_at->format('d/m/Y à H:i') }}
            @endif
        </div>
    </div>

    @if($demande->isAnnotated())
        <div class="annotation-box">
            <div class="annotation-header">
                <span class="annotation-badge">Annotation Direction</span>
                @if($demande->folder)
                    <span class="annotation-folder">Classé : {{ ucfirst($demande->folder) }}</span>
                @endif
            </div>
            <p class="annotation-text">{{ $demande->annotation }}</p>
            <p class="annotation-meta">
                Annoté par {{ $demande->annotatedBy?->name ?? 'Direction' }}
                le {{ $demande->annotated_at->format('d/m/Y à H:i') }}
            </p>
        </div>
    @endif

    @php $d = $demande->data ?? []; @endphp

    <div class="section">
        <div class="section-header">Informations générales</div>
        <div class="section-body">
            <table class="info info-2col">
                <tr>
                    <td class="label">N° Dossier</td>
                    <td class="value">{{ $demande->code }}</td>
                    <td class="label">Statut</td>
                    <td class="value">{{ $demande->status->label ?? $demande->status->code }}</td>
                </tr>
                <tr>
                    <td class="label">Institution</td>
                    <td class="value" colspan="3">{{ $d['institution'] ?? ($demande->user?->name ?? '—') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-header">Identité du demandeur</div>
        <div class="section-body">
            <table class="info info-2col">
                <tr>
                    <td class="label">Nom</td>
                    <td class="value">{{ $d['lastname'] ?? '—' }}</td>
                    <td class="label">Prénom</td>
                    <td class="value">{{ $d['firstname'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Nom de la mère</td>
                    <td class="value">{{ $d['mother_lastname'] ?? '—' }}</td>
                    <td class="label">Prénom de la mère</td>
                    <td class="value">{{ $d['mother_firstname'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Lieu de naissance</td>
                    <td class="value">{{ $d['birth_place'] ?? '—' }}</td>
                    <td class="label">Date de naissance</td>
                    <td class="value">{{ $d['birth_date'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">NIF</td>
                    <td class="value">{{ $d['nif'] ?? '—' }}</td>
                    <td class="label">NINU</td>
                    <td class="value">{{ $d['ninu'] ?? '—' }}</td>
                </tr>
                @if(!empty($d['spouse_lastname']) || !empty($d['spouse_firstname']))
                <tr>
                    <td class="label">Conjoint(e)</td>
                    <td class="value" colspan="3">{{ trim(($d['spouse_lastname'] ?? '') . ' ' . ($d['spouse_firstname'] ?? '')) ?: '—' }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-header">Informations professionnelles</div>
        <div class="section-body">
            <table class="info info-2col">
                <tr>
                    <td class="label">Date d'entrée en service</td>
                    <td class="value">{{ $d['entry_date'] ?? '—' }}</td>
                    <td class="label">Salaire actuel</td>
                    <td class="value">{{ $d['current_salary'] ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if(!empty($d['dependents']) && is_array($d['dependents']))
        <div class="section">
            <div class="section-header">Personnes à charge ({{ count($d['dependents']) }})</div>
            <div class="section-body">
                <table class="docs">
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Lien</th>
                        <th>Date de naissance</th>
                    </tr>
                    @foreach($d['dependents'] as $dep)
                        <tr>
                            <td>{{ $dep['nom'] ?? $dep['lastname'] ?? '—' }}</td>
                            <td>{{ $dep['prenom'] ?? $dep['firstname'] ?? '—' }}</td>
                            <td>{{ $dep['lien'] ?? $dep['relation'] ?? '—' }}</td>
                            <td>{{ $dep['date_naissance'] ?? $dep['birth_date'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif

    @if(!empty($d['previous_jobs']) && is_array($d['previous_jobs']))
        <div class="section">
            <div class="section-header">Emplois précédents ({{ count($d['previous_jobs']) }})</div>
            <div class="section-body">
                <table class="docs">
                    <tr>
                        <th>Employeur</th>
                        <th>Poste</th>
                        <th>Période</th>
                    </tr>
                    @foreach($d['previous_jobs'] as $job)
                        <tr>
                            <td>{{ $job['employeur'] ?? $job['employer'] ?? '—' }}</td>
                            <td>{{ $job['poste'] ?? $job['position'] ?? '—' }}</td>
                            <td>{{ ($job['date_debut'] ?? $job['from'] ?? '—') . ' – ' . ($job['date_fin'] ?? $job['to'] ?? '—') }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif

    @if($demande->documents->isNotEmpty())
        <div class="section">
            <div class="section-header">Documents joints ({{ $demande->documents->count() }})</div>
            <div class="section-body">
                <table class="docs">
                    <tr>
                        <th style="width:55%">Nom du fichier</th>
                        <th style="width:25%">Type</th>
                        <th style="width:20%">Taille</th>
                    </tr>
                    @foreach($demande->documents as $doc)
                        <tr>
                            <td>{{ $doc->original_name }}</td>
                            <td>{{ str_replace('_', ' ', ucwords($doc->label ?? $doc->type, '_')) }}</td>
                            <td>{{ $doc->size ? number_format($doc->size / 1024, 1) . ' Ko' : '—' }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif

    <div class="signature-zone">
        <div class="sig-block">
            <div class="sig-label">Signature du demandeur</div>
            <div class="sig-name">{{ $demande->user?->name ?? '—' }}</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Visa — Direction des Pensions Civiles</div>
            <div class="sig-name">{{ $demande->annotatedBy?->name ?? '' }}</div>
        </div>
    </div>

</div>

<div class="footer">
    <div class="footer-left">Direction des Pensions Civiles — République d'Haïti</div>
    <div class="footer-center">Document officiel généré automatiquement — Ne pas modifier</div>
    <div class="footer-right">Généré le {{ now()->format('d/m/Y à H:i') }}<br>Réf : {{ $demande->code }}</div>
</div>

</body>
</html>
