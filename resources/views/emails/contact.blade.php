<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; background: #f8fafc; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 18px; font-weight: bold; }
        .header p { margin: 4px 0 0; font-size: 12px; opacity: 0.85; }
        .body { padding: 32px; }
        .row { margin-bottom: 16px; }
        .label { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px; }
        .value { font-size: 14px; color: #1e293b; background: #f1f5f9; padding: 10px 14px; border-radius: 6px; }
        .message-value { white-space: pre-wrap; line-height: 1.6; }
        .footer { background: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 16px 32px; font-size: 11px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Nouveau message de contact</h1>
        <p>Direction des Pensions Civiles — République d'Haïti</p>
    </div>
    <div class="body">
        <div class="row">
            <div class="label">Expéditeur</div>
            <div class="value">{{ $contact->first_name }} {{ $contact->last_name }}</div>
        </div>
        <div class="row">
            <div class="label">Adresse e-mail</div>
            <div class="value">{{ $contact->email }}</div>
        </div>
        <div class="row">
            <div class="label">Sujet</div>
            <div class="value">
                @php
                    $subjects = [
                        'pension'    => 'Question sur les pensions',
                        'documents'  => 'Demande de documents',
                        'rendezvous' => 'Prise de rendez-vous',
                        'autre'      => 'Autre',
                    ];
                @endphp
                {{ $subjects[$contact->subject] ?? $contact->subject }}
            </div>
        </div>
        <div class="row">
            <div class="label">Message</div>
            <div class="value message-value">{{ $contact->message }}</div>
        </div>
        <p style="font-size:12px;color:#64748b;margin-top:24px;">
            Reçu le {{ $contact->created_at->format('d/m/Y à H:i') }}. Pour répondre, utilisez le bouton Répondre — l'adresse de l'expéditeur est déjà configurée.
        </p>
    </div>
    <div class="footer">Direction des Pensions Civiles · Generé automatiquement — ne pas répondre directement à cet e-mail</div>
</div>
</body>
</html>
