<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $demande->code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            background: #fff;
        }

        /* ======= PAGE LAYOUT ======= */
        .page {
            padding: 28px 32px 90px 32px;
            position: relative;
            min-height: 100%;
        }

        /* ======= HEADER ======= */
        .header {
            margin-bottom: 22px;
            padding-bottom: 14px;
            border-bottom: 3px solid #0f4c91;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .header-left { flex: 1; }
        .header-left .republic {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .header-left .institution {
            font-size: 15px;
            font-weight: bold;
            color: #0f4c91;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .header-left .subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .header-right {
            text-align: right;
        }
        .doc-type-badge {
            display: inline-block;
            background: #0f4c91;
            color: #fff;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 3px 10px;
            border-radius: 3px;
            margin-bottom: 6px;
        }
        .ref-block {
            font-size: 8.5px;
            color: #475569;
            line-height: 1.7;
        }
        .ref-block strong { color: #1e293b; }

        /* ======= STATUS BAR ======= */
        .status-bar {
            background: #f1f5f9;
            border-left: 4px solid #0f4c91;
            padding: 7px 12px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-bar .status-label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .status-bar .status-value {
            font-size: 10px;
            font-weight: bold;
            color: #0f4c91;
        }
        .status-bar .date-info { font-size: 8.5px; color: #64748b; text-align: right; }

        /* ======= ANNOTATION ======= */
        .annotation-box {
            border: 1px solid #f59e0b;
            background: #fffbeb;
            border-radius: 4px;
            padding: 10px 12px;
            margin-bottom: 18px;
        }
        .annotation-header {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
        }
        .annotation-badge {
            background: #f59e0b;
            color: #fff;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 2px;
            margin-right: 8px;
        }
        .annotation-folder {
            font-size: 8.5px;
            color: #92400e;
            font-style: italic;
        }
        .annotation-text { font-size: 10px; color: #78350f; line-height: 1.5; }
        .annotation-meta { font-size: 8px; color: #b45309; margin-top: 5px; }

        /* ======= SECTIONS ======= */
        .section { margin-bottom: 18px; }
        .section-header {
            background: #0f4c91;
            color: #fff;
            padding: 5px 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0;
            border-radius: 2px 2px 0 0;
        }
        .section-body {
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 2px 2px;
            overflow: hidden;
        }

        /* ======= INFO TABLE ======= */
        table.info {
            width: 100%;
            border-collapse: collapse;
        }
        table.info tr:nth-child(even) td { background: #f8fafc; }
        table.info td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            line-height: 1.4;
        }
        table.info tr:last-child td { border-bottom: none; }
        table.info td.label {
            font-weight: bold;
            font-size: 9px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            width: 35%;
            background: #f1f5f9;
        }
        table.info td.value {
            font-size: 10px;
            color: #1e293b;
        }

        /* ======= TWO-COL TABLE ======= */
        table.info-2col td.label { width: 22%; }

        /* ======= DOCUMENTS TABLE ======= */
        table.docs {
            width: 100%;
            border-collapse: collapse;
        }
        table.docs th {
            background: #e2e8f0;
            font-size: 8.5px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            padding: 5px 8px;
            text-align: left;
            border-bottom: 1px solid #cbd5e1;
        }
        table.docs td {
            padding: 5px 8px;
            font-size: 9.5px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }
        table.docs tr:last-child td { border-bottom: none; }
        table.docs tr:nth-child(even) td { background: #f8fafc; }

        /* ======= DIVIDER ======= */
        .divider {
            border: none;
            border-top: 1px dashed #e2e8f0;
            margin: 14px 0;
        }

        /* ======= SIGNATURE ZONE ======= */
        .signature-zone {
            margin-top: 22px;
            display: flex;
            justify-content: space-between;
        }
        .sig-block {
            width: 42%;
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
            text-align: center;
        }
        .sig-block .sig-label { font-size: 8.5px; color: #64748b; text-transform: uppercase; }
        .sig-block .sig-name { font-size: 9.5px; font-weight: bold; color: #1e293b; margin-top: 4px; }

        /* ======= FOOTER ======= */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px 32px;
            background: #f8fafc;
            border-top: 2px solid #0f4c91;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer-left { font-size: 7.5px; color: #64748b; }
        .footer-center {
            font-size: 7.5px;
            color: #94a3b8;
            font-style: italic;
            text-align: center;
        }
        .footer-right { font-size: 7.5px; color: #64748b; text-align: right; }

        /* ======= CONFIDENTIAL WATERMARK ======= */
        .confidential {
            position: fixed;
            top: 45%;
            left: 10%;
            width: 80%;
            text-align: center;
            font-size: 55px;
            font-weight: bold;
            color: rgba(15, 76, 145, 0.04);
            text-transform: uppercase;
            letter-spacing: 10px;
            transform: rotate(-30deg);
            pointer-events: none;
        }

        /* ======= PAGE NUMBER ======= */
        .page-num:after { content: counter(page); }
        .page-count:after { content: counter(pages); }
    </style>
</head>
<body>

<div class="confidential">Direction des Pensions Civiles</div>

<div class="page">

    {{-- ===== EN-TÊTE ===== --}}
    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <p class="republic">République d'Haïti — Ministère de l'Économie et des Finances</p>
                <p class="institution">Direction des Pensions Civiles</p>
                <p class="subtitle">Gestion des dossiers de pension du secteur public</p>
            </div>
            <div class="header-right">
                <span class="doc-type-badge">
                    {{ str_replace('_', ' ', ucwords(strtolower($demande->type), '_')) }}
                </span>
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

    {{-- ===== BARRE DE STATUT ===== --}}
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

    {{-- ===== ANNOTATION DE LA DIRECTION ===== --}}
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

    {{-- ===== INFORMATIONS GÉNÉRALES ===== --}}
    <div class="section">
        <div class="section-header">Informations générales</div>
        <div class="section-body">
            <table class="info info-2col">
                <tr>
                    <td class="label">N° Dossier</td>
                    <td class="value">{{ $demande->code }}</td>
                    <td class="label">Type</td>
                    <td class="value">{{ str_replace('_', ' ', ucwords(strtolower($demande->type), '_')) }}</td>
                </tr>
                <tr>
                    <td class="label">Déposé par</td>
                    <td class="value">{{ $demande->user?->name ?? '—' }}</td>
                    <td class="label">Statut</td>
                    <td class="value">{{ $demande->status->label ?? $demande->status->code }}</td>
                </tr>
                <tr>
                    <td class="label">Service</td>
                    <td class="value" colspan="3">{{ $demande->service?->nom ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ===== DONNÉES DU FORMULAIRE ===== --}}
    @if(!empty($demande->data))
        <div class="section">
            <div class="section-header">Données de la demande</div>
            <div class="section-body">
                <table class="info">
                    @foreach($demande->data as $key => $value)
                        @if($key === 'documents' || $key === 'pieces')
                            {{-- skip embedded file paths --}}
                        @elseif(is_array($value) && count($value) > 0)
                            <tr>
                                <td class="label">{{ str_replace('_', ' ', ucwords($key, '_')) }}</td>
                                <td class="value">
                                    @foreach($value as $i => $item)
                                        @if(is_array($item))
                                            {{ implode(', ', array_filter($item, fn($v) => !is_array($v) && $v !== '')) }}
                                        @else
                                            {{ $item }}
                                        @endif
                                        @if(!$loop->last) — @endif
                                    @endforeach
                                </td>
                            </tr>
                        @elseif(!is_array($value) && !is_null($value) && $value !== '')
                            <tr>
                                <td class="label">{{ str_replace('_', ' ', ucwords($key, '_')) }}</td>
                                <td class="value">{{ $value }}</td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>
        </div>
    @endif

    {{-- ===== DOCUMENTS JOINTS ===== --}}
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

    {{-- ===== ZONE DE SIGNATURE ===== --}}
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

{{-- ===== PIED DE PAGE ===== --}}
<div class="footer">
    <div class="footer-left">
        Direction des Pensions Civiles — République d'Haïti
    </div>
    <div class="footer-center">
        Document officiel généré automatiquement — Ne pas modifier
    </div>
    <div class="footer-right">
        Généré le {{ now()->format('d/m/Y à H:i') }}<br>
        Réf : {{ $demande->code }}
    </div>
</div>

</body>
</html>
