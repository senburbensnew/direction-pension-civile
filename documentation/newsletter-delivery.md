# Newsletter — livraison et authentification

## Configuration applicative

Variables utiles (`.env`) :

- `NEWSLETTER_DOUBLE_OPT_IN=true` — confirmation par e-mail avant inscription active
- `NEWSLETTER_BATCH_SIZE=50` — taille des lots d’envoi file d’attente
- `NEWSLETTER_WEBHOOK_SECRET=` — secret pour `POST /webhooks/newsletter` (événements `bounce`, `delivered`)
- `NEWSLETTER_OPEN_TRACKING=false` / `NEWSLETTER_CLICK_TRACKING=false` — métriques optionnelles
- `NEWSLETTER_FROM_NAME`, `NEWSLETTER_REPLY_TO` — en-têtes campagne

Planification : `php artisan schedule:work` ou cron Laravel exécute `newsletter:dispatch-scheduled` chaque minute.

File d’attente : les envois passent par `SendNewsletterDeliveryJob` — lancer `php artisan queue:work`.

## Webhook générique

```json
POST /webhooks/newsletter
Header: X-Newsletter-Webhook-Secret: <secret>
{
  "event": "bounce",
  "email": "user@example.com",
  "message_id": "optional-provider-id"
}
```

## SPF, DKIM, DMARC

Ces enregistrements se configurent sur le **domaine d’envoi** (DNS), pas dans le code :

1. **SPF** — autoriser votre SMTP / ESP (`v=spf1 include:... ~all`)
2. **DKIM** — clé publique fournie par Mailgun, SES, Postmark, etc.
3. **DMARC** — politique alignée (`p=quarantine` ou `p=reject` après validation)

Utilisez la même adresse `MAIL_FROM_ADDRESS` que celle authentifiée chez votre fournisseur.

## Rétention

`NEWSLETTER_RETENTION_DAYS` (défaut 730) — documenter la politique de confidentialité côté site public ; purge manuelle ou commande future sur `newsletter_deliveries` / activity log.
