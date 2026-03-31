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
                <span class="doc-type-badge">Demande de pension de réversion</span>
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
                    <td class="label">Déposé par</td>
                    <td class="value">{{ $demande->user?->name ?? '—' }}</td>
                    <td class="label">Service</td>
                    <td class="value">{{ $demande->service?->nom ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-header">Informations sur le défunt</div>
        <div class="section-body">
            <table class="info">
                <tr>
                    <td class="label">Nom complet du défunt</td>
                    <td class="value">{{ $d['nom_complet_defunt'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Numéro de pension</td>
                    <td class="value">{{ $d['numero_pension'] ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-header">Informations sur le bénéficiaire</div>
        <div class="section-body">
            <table class="info info-2col">
                <tr>
                    <td class="label">Nom du bénéficiaire</td>
                    <td class="value">{{ $d['nom_beneficiaire'] ?? '—' }}</td>
                    <td class="label">Lien avec le défunt</td>
                    <td class="value">{{ $d['relation_defunt'] ?? '—' }}</td>
                </tr>
                @if(!empty($d['matricule_fiscal']))
                <tr>
                    <td class="label">Matricule fiscal</td>
                    <td class="value" colspan="3">{{ $d['matricule_fiscal'] }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

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
