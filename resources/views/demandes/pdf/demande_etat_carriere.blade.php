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

    {{-- EN-TÊTE --}}
    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <p class="republic">République d'Haïti — Ministère de l'Économie et des Finances</p>
                <p class="institution">Direction des Pensions Civiles</p>
                <p class="subtitle">Gestion des dossiers de pension du secteur public</p>
            </div>
            <div class="header-right">
                <span class="doc-type-badge">Demande d'état de carrière</span>
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

    {{-- BARRE DE STATUT --}}
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

    {{-- ANNOTATION --}}
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

    {{-- INFORMATIONS GÉNÉRALES --}}
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
                    <td class="label">Déposé par</td>
                    <td class="value">{{ $demande->user?->name ?? '—' }}</td>
                    <td class="label">Service</td>
                    <td class="value">{{ $demande->service?->nom ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- IDENTITÉ DU FONCTIONNAIRE --}}
    <div class="section">
        <div class="section-header">Identité du fonctionnaire</div>
        <div class="section-body">
            <table class="info info-2col">
                <tr>
                    <td class="label">Nom</td>
                    <td class="value">{{ $d['nom'] ?? '—' }}</td>
                    <td class="label">Prénom</td>
                    <td class="value">{{ $d['prenom'] ?? '—' }}</td>
                </tr>
                @if(!empty($d['nom_jeune_fille']))
                <tr>
                    <td class="label">Nom de jeune fille</td>
                    <td class="value" colspan="3">{{ $d['nom_jeune_fille'] }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Date de naissance</td>
                    <td class="value">{{ $d['date_naissance'] ?? '—' }}</td>
                    <td class="label">Lieu de naissance</td>
                    <td class="value">{{ $d['lieu_naissance'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">État civil</td>
                    <td class="value">{{ $d['etat_civil'] ?? '—' }}</td>
                    <td class="label">Statut</td>
                    <td class="value">{{ $d['statut'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">NIF / NINU</td>
                    <td class="value">{{ $d['nif_ninu'] ?? '—' }}</td>
                    <td class="label">CIN</td>
                    <td class="value">{{ $d['cin'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Adresse</td>
                    <td class="value">{{ $d['adresse'] ?? '—' }}</td>
                    <td class="label">Téléphone</td>
                    <td class="value">{{ $d['telephone'] ?? '—' }}</td>
                </tr>
                @if(!empty($d['email']))
                <tr>
                    <td class="label">Courriel</td>
                    <td class="value" colspan="3">{{ $d['email'] }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- INFORMATIONS PROFESSIONNELLES --}}
    <div class="section">
        <div class="section-header">Informations professionnelles</div>
        <div class="section-body">
            <table class="info info-2col">
                <tr>
                    <td class="label">Employeur</td>
                    <td class="value">{{ $d['employeur'] ?? '—' }}</td>
                    <td class="label">Fonction</td>
                    <td class="value">{{ $d['fonction'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Date début de service</td>
                    <td class="value">{{ $d['date_debut_service'] ?? '—' }}</td>
                    <td class="label">Date fin de service</td>
                    <td class="value">{{ $d['date_fin_service'] ?? '—' }}</td>
                </tr>
                @if(!empty($d['numero_dossier']))
                <tr>
                    <td class="label">N° de dossier</td>
                    <td class="value" colspan="3">{{ $d['numero_dossier'] }}</td>
                </tr>
                @endif
                @if(!empty($d['raison']))
                <tr>
                    <td class="label">Motif de la demande</td>
                    <td class="value" colspan="3">{{ $d['raison'] }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- DOCUMENTS JOINTS --}}
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

    {{-- ZONE DE SIGNATURE --}}
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
