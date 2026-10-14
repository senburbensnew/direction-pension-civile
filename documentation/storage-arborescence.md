# Arborescence `storage/`

Référence alignée sur `config/uploads.php` et les commandes `uploads:*`.

## Vue d’ensemble

| Racine | Disque Laravel | Accès web |
|--------|----------------|-----------|
| `storage/app/public/` | `public` | Oui — via `public/storage` → `/storage/…` |
| `storage/app/private/` | `private` | Non — pièces sensibles, Media Library métier |
| `storage/app/secrets/` | (fichiers locaux) | Non — clés API, credentials |
| `storage/framework/`, `storage/logs/` | — | Laravel (cache, sessions, logs) |

Les contenus **CMS** (site vitrine + admin) sont sous **`storage/app/public/cms/`**.  
Les **assets UI** (drapeaux, logo, placeholder) restent dans **`public/images/`** (hors storage).

---

## `storage/app/public/` (disque `public`)

```
public/                          # racine du disque (= storage/app/public)
├── cms/
│   ├── actualites/              # images des actualités (upload admin)
│   ├── carousel/                # slides page d’accueil
│   ├── institution/             # visuels institutionnels (hero, organigramme, …)
│   │   └── historique/          # page Missions — archives historiques
│   ├── mediatheque/
│   │   ├── image/               # type MediathequeItem: image (singulier, pas « images »)
│   │   ├── video/
│   │   ├── audio/
│   │   └── document/
│   ├── officials/               # photos ministre / direction
│   ├── partenaires/             # logos partenaires
│   ├── publications/            # PDF/DOC textes officiels
│   └── reports/
│       └── {année}/             # rapports PDF par année (ex. 2026/)
└── users/
    └── profile-photos/          # avatars utilisateurs
```

**URL type :** `https://…/storage/cms/carousel/KEV_6804.jpg`

---

## `storage/app/private/` (disque `private`)

```
private/
├── media/                       # Spatie Media Library (StructuredMediaPathGenerator)
│   ├── demandes/{id}/{collection}/{media_id}/
│   ├── comptes/{id}/…
│   ├── mandats/{id}/…
│   └── beneficiaire-relations/{id}/…
├── temp/
│   └── form-stash/              # brouillons upload formulaires (session)
├── demandes/
│   └── embedded/{type}/{year}/{month}/   # fichiers référencés dans demandes.data
└── secrets/                     # optionnel si déplacé depuis app/secrets
```

Les chemins Media Library sont définis dans `config/uploads.php` → `media_folders`.

---

## `storage/app/secrets/`

Fichiers de credentials (OCR, Google, etc.) — **ne jamais committer**.

---

## Commandes Artisan

| Commande | Rôle |
|----------|------|
| `php artisan uploads:ensure-structure` | Crée les dossiers canoniques (`.gitkeep`) |
| `php artisan uploads:migrate-structure` | Migre legacy `public/documents`, `public/images`, anciens préfixes storage |
| `php artisan uploads:prune-legacy` | Supprime dossiers legacy vides, temporaires, doublons |

---

## Anciens emplacements (obsolètes)

Ne plus utiliser :

- `public/documents/`, `public/media/`
- `public/images/carousel/`, `public/images/historique/`, logos CMS à la racine de `public/images/`
- `storage/app/public/mediatheque/`, `reports/`, `carousel/` (sans préfixe `cms/`)
- `storage/app/public/images/`

`CmsPathResolver` traduit encore les chemins legacy en base vers `cms/…` le temps de la transition.
