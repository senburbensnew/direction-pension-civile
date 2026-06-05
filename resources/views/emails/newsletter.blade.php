<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campaign->subject }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f1f5f9; font-family: 'Segoe UI', Arial, sans-serif; color: #1e293b; }
        .wrapper { max-width: 620px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .header { background: #0f2340; padding: 28px 32px; text-align: center; }
        .header img { height: 40px; margin-bottom: 10px; }
        .header h1 { margin: 0; color: #ffffff; font-size: 18px; font-weight: 600; }
        .header p { margin: 4px 0 0; color: #93c5fd; font-size: 13px; }
        .body { padding: 32px; line-height: 1.7; font-size: 15px; }
        .body h2 { font-size: 20px; font-weight: 700; color: #0f2340; margin: 0 0 16px; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; text-align: center; }
        .footer p { margin: 0; color: #94a3b8; font-size: 12px; line-height: 1.6; }
        .footer a { color: #3b82f6; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Direction de la Pension Civile</h1>
            <p>Newsletter officielle</p>
        </div>

        <div class="body">
            <h2>{{ $campaign->subject }}</h2>
            {!! nl2br(e($campaign->body)) !!}
        </div>

        <div class="footer">
            <p>
                Vous recevez cet email car vous êtes abonné à la newsletter de la Direction de la Pension Civile.<br>
                <a href="{{ route('newsletter.unsubscribe', $subscriber->unsubscribe_token) }}">
                    Se désabonner
                </a>
            </p>
        </div>
    </div>
</body>
</html>
