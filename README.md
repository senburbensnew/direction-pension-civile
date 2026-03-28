# Système de Gestion des Pensions Civiles

Application web de gestion des demandes de pension civile en Haïti, développée avec Laravel 10.

## Présentation

Cette application permet aux pensionnaires, fonctionnaires et institutions de soumettre et suivre leurs demandes administratives liées aux pensions civiles. Elle offre également aux agents et administrateurs un tableau de bord complet pour gérer les dossiers, les transferts entre services et les workflows de validation.

### Types d'utilisateurs

| Rôle | Description |
|------|-------------|
| **Pensionnaire** | Retraités soumettant des demandes de virement, attestations, preuves d'existence, etc. |
| **Fonctionnaire** | Agents civils en activité demandant leur mise à la retraite ou un relevé de carrière |
| **Institution** | Organismes soumettant des demandes d'adhésion |
| **Agent / Admin** | Personnel interne gérant les dossiers et les workflows |

### Types de demandes

- Virement bancaire
- Attestation de pension
- Transfert de chèque
- Arrêt de paiement
- Preuve d'existence
- Pension de survivant
- Demande de pension (fonctionnaire)
- Relevé de carrière
- Adhésion institutionnelle

## Stack technique

- **Backend** : Laravel 10, PHP 8.x
- **Frontend** : Blade, Alpine.js, Tailwind CSS, DaisyUI
- **Base de données** : MySQL
- **Assets** : Vite
- **Autorisations** : Spatie Laravel Permission
- **PDF** : DomPDF (via Laravel)

## Installation

### Prérequis

- PHP 8.1+
- Composer
- Node.js 18+ et npm
- MySQL 8+

### Étapes

```bash
# 1. Cloner le dépôt
git clone <repo-url>
cd direction-pension-civile

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances JavaScript
npm install

# 4. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 5. Configurer la base de données dans .env
# DB_DATABASE=pension_civile
# DB_USERNAME=...
# DB_PASSWORD=...

# 6. Exécuter les migrations et les seeders
php artisan migrate --seed

# 7. Lier le stockage public
php artisan storage:link
```

## Développement

```bash
# Démarrer le serveur de développement
php artisan serve

# Compiler les assets (mode watch)
npm run dev

# Compiler pour la production
npm run build
```

## Tests

```bash
# Lancer tous les tests
php artisan test

# Lancer un fichier de test spécifique
php artisan test tests/Feature/ExampleTest.php
```

## Qualité du code

```bash
# Formater le code (Laravel Pint)
./vendor/bin/pint
```

## Architecture

### Modèle central : Demande

Toutes les demandes sont stockées dans une table `demandes` unifiée avec une colonne `type` et une colonne JSON `data` pour les champs spécifiques à chaque type. Voir [app/Models/Demande.php](app/Models/Demande.php).

### Workflow

Deux systèmes de suivi parallèles :
- `DemandeWorkflow` — enregistre les transferts entre services
- `DemandeHistory` — enregistre tous les changements de statut avec acteur et commentaires

Les statuts sont stockés dans la table `statuses` et identifiés par un code (`BROUILLON`, `SOUMISE`, `TRANSFEREE`, etc.).

### Documents

Les pièces jointes sont configurées par type de demande dans [config/demandes.php](config/demandes.php) (labels, min/max fichiers, types autorisés).

### Validation Haïtienne

Règles de validation spécifiques dans [app/Rules/](app/Rules/) : `Nif`, `Ninu`, `CodePension`, `Cin`, `Telephone`.

### Localisation

L'application supporte le français et l'anglais. Les fichiers de traduction sont dans [lang/](lang/).

## Structure des dossiers clés

```
app/
├── Enums/              # UserTypeEnum, TypeDemandeEnum
├── Http/
│   ├── Controllers/    # DemandeController, DemandeManagementController, ...
│   └── Middleware/     # Admin, CorbeilleAccess, SetLocale
├── Models/             # Demande, DemandeDocument, DemandeWorkflow, ...
├── Policies/           # DemandePolicy
├── Rules/              # Validation haïtienne (Nif, Ninu, Cin, ...)
└── Services/           # DemandeService, DemandeWorkflowService
config/
└── demandes.php        # Configuration des types de demandes et documents
resources/views/
├── demandes/           # Formulaires et détails des demandes
├── admin/              # Interface d'administration
├── components/         # Composants Blade réutilisables
└── layouts/            # Layouts principaux
```

## Licence

Propriétaire — Direction des Pensions Civiles, République d'Haïti.
