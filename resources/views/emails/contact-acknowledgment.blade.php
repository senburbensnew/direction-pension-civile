<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Source Sans 3", Arial, Helvetica, sans-serif; font-size: 14px; color: #1e293b; background: #f8fafc; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 18px; font-weight: bold; }
        .header p { margin: 4px 0 0; font-size: 12px; opacity: 0.85; }
        .body { padding: 32px; line-height: 1.6; }
        .notice { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 6px; padding: 14px 16px; font-size: 13px; color: #9a3412; margin-top: 20px; }
        .footer { background: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 16px 32px; font-size: 11px; color: #94a3b8; text-align: center; }
        a { color: #1e40af; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Votre message a bien été enregistré</h1>
        <p>Direction de la Pension Civile — République d'Haïti</p>
    </div>
    <div class="body">
        <p>Bonjour {{ $contact->first_name }},</p>
        <p>
            Nous avons bien reçu votre message concernant
            <strong>{{ $contact->subjectLabel() }}</strong>.
        </p>
        <p>
            Notre équipe vous répondra dans les meilleurs délais à l’adresse
            <strong>{{ $contact->email }}</strong>.
        </p>
        <p>
            Pour le suivi d’un dossier de pension ou toute démarche nécessitant des pièces officielles,
            connectez-vous à votre
            <a href="{{ route('login') }}">espace sécurisé sur le portail DPC</a>.
        </p>
        <div class="notice">
            <strong>Important :</strong> ne transmettez jamais par ce formulaire public votre NIF,
            des pièces d’identité, des relevés bancaires ou tout autre document sensible.
        </div>
    </div>
    <div class="footer">Direction de la Pension Civile · Message automatique — merci de ne pas répondre à cet e-mail</div>
</div>
</body>
</html>
