<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Site en maintenance — Direction de la Pension Civile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f2340;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            color: #e2e8f0;
        }

        .card {
            background: #1a3258;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 1.25rem;
            padding: 3rem 3.5rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,.4);
        }

        .icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(251,191,36,.12);
            border: 2px solid rgba(251,191,36,.3);
            margin-bottom: 1.75rem;
        }

        .icon-wrap i {
            font-size: 2rem;
            color: #fbbf24;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: .75rem;
        }

        .subtitle {
            font-size: .95rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .divider {
            height: 1px;
            background: rgba(255,255,255,.08);
            margin: 1.75rem 0;
        }

        .contact-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-size: .85rem;
            color: #94a3b8;
            margin-bottom: .6rem;
        }

        .contact-row i {
            width: 1.1rem;
            text-align: center;
            color: #60a5fa;
            flex-shrink: 0;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .75rem;
            font-weight: 600;
            color: #fbbf24;
            background: rgba(251,191,36,.1);
            border: 1px solid rgba(251,191,36,.25);
            border-radius: 2rem;
            padding: .35rem .9rem;
            margin-bottom: 1.5rem;
        }

        .badge i { font-size: .7rem; }

        footer-note {
            display: block;
            margin-top: 2.5rem;
            font-size: .75rem;
            color: #475569;
        }

        @media (max-width: 480px) {
            .card { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="icon-wrap">
            <i class="fas fa-tools"></i>
        </div>

        <div class="badge">
            <i class="fas fa-circle" style="font-size:.5rem;color:#fbbf24;"></i>
            Maintenance en cours
        </div>

        <h1>Site temporairement indisponible</h1>

        <p class="subtitle">
            Nous effectuons des travaux de maintenance afin d'améliorer votre expérience.
            Le service sera de nouveau disponible très prochainement.
        </p>

        <div class="divider"></div>

        <div style="text-align:left">
            <p style="font-size:.8rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.85rem;">Contact</p>
            <div class="contact-row">
                <i class="fas fa-envelope"></i>
                <span>dpc.info@mef.gouv.ht</span>
            </div>
            <div class="contact-row">
                <i class="fas fa-phone"></i>
                <span>+(509) 29 92 1007</span>
            </div>
            <div class="contact-row">
                <i class="fas fa-clock"></i>
                <span>Lun–Ven : 8h00 – 16h00</span>
            </div>
        </div>
    </div>

    <footer-note>Direction de la Pension Civile &mdash; République d'Haïti</footer-note>

</body>
</html>
