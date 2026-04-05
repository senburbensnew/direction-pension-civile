# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

```bash
# Start development server
php artisan serve

# Build frontend assets (watch mode)
npm run dev

# Build for production
npm run build

# Run all tests
php artisan test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed

# Code style (Laravel Pint)
./vendor/bin/pint
```

## Architecture Overview

This is a **Haitian civil pension management system** built with Laravel 10. The application serves three user types (defined in `app/Enums/UserTypeEnum.php`): **Pensionnaire** (retirees), **Fonctionnaire** (civil servants), and **Institution**.

### Core Domain: Demandes (Requests)

The central concept is a `Demande` (request/application). All request types are unified under a single `demandes` table with a `type` column (values from `app/Enums/TypeDemandeEnum.php`) and a JSON `data` column for type-specific fields.

- **Request types by user role:**
  - Pensionnaire: bank transfers, attestations, check transfers, payment stops, proof of existence, survivor pension
  - Fonctionnaire: pension request, career statement
  - Institution: membership adhesion

- `app/Models/Demande.php` — central model; `data` is cast to array for flexible per-type storage
- `app/Services/DemandeService.php` — creation/update business logic
- `app/Services/DemandeWorkflowService.php` — handles status transitions (submit → Reception service → transfer between internal services)
- `app/Http/Controllers/DemandeController.php` — user-facing CRUD
- `app/Http/Controllers/DemandeManagementController.php` — admin/agent-facing management

### Document Attachments

Documents are attached to demandes via `DemandeDocument` model. Document requirements per demande type (labels, min/max file counts, single vs. multiple) are configured in `config/demandes.php`, not hardcoded. The `disk` key in that config (currently `public`) controls storage location.

### Workflow / Status Tracking

Two parallel tracking systems exist:
- `DemandeWorkflow` — records service-to-service transfers (from/to `Service` model IDs)
- `DemandeHistory` — records all status changes with actor and comments

Status values live in the `statuses` table (not an enum), identified by a `code` string (e.g., `BROUILLON`, `SOUMISE`, `TRANSFEREE`). The `Status` model has static helpers like `Status::SUBMITTED` for ID lookups.

### Authorization

- **Spatie Laravel Permission** handles roles and permissions
- `app/Policies/DemandePolicy.php` — request-level authorization
- Custom middleware: `Admin` (role check), `CorbeilleAccess` (trash section), `SetLocale` (language switching)
- Admin routes require `role:admin` middleware; regular demande routes require `auth`

### Frontend Stack

- **Blade** templates with **Alpine.js** for reactivity
- **Tailwind CSS** + **DaisyUI** component library
- **Vite** for asset bundling (entry: `resources/css/app.css`, `resources/js/app.js`)
- **Signature Pad** for digital signatures on forms
- Views are organized by domain under `resources/views/` (e.g., `demandes/`, `fonctionnaire/`, `institution/`, `pensionnaire/`, `personal/`, `admin/`)

### Custom Validation

Haitian-specific validation rules in `app/Rules/`: `Nif` (tax ID), `Ninu` (social security), `CodePension`, `Cin` (national ID), `Telephone`.

### Localization

The app supports French/English. Language files are in `lang/`. The `SetLocale` middleware reads the session locale set via `GET /locale/{locale}`.

## Session Log — 2026-03-28

### Completed

**Route fixes (admin prefix)**
- Fixed double-prefix bug in `routes/web.php`: routes inside `Route::prefix('personal')->name('personal.')` had redundant `personal.` in their `.name()` calls — removed the prefix from inner route names.
- Fixed `admin.actualites.*` and `admin.reports.*` route references in controllers and views after routes were restructured under the `admin.` prefix group.

**UI / Alpine.js**
- Added `[x-cloak] { display: none !important; }` to `resources/css/app.css` — fixes notification modal and dropdown flashing on page load.
- Added `x-cloak` to the notification dropdown in `resources/views/components/navbar.blade.php`.
- Made **Messages du dossier**, **Historique de la demande**, and **Journal d'activité** sections in `request-details.blade.php` collapsible and closed by default using Alpine.js `x-data="{ open: false }"` + `x-show` + `x-transition` + rotating chevron.
- Fixed extra `</div>` in `request-details.blade.php` that caused all content after "Messages du dossier" to render outside the layout containers.

**Notifications**
- Reordered `via()` in all 3 notification classes (`DemandeStatusChangedNotification`, `DemandeSubmittedNotification`, `DemandeTransferredNotification`) from `['mail', 'database']` → `['database', 'mail']`. This ensures in-app notifications are always saved even when SMTP is unavailable.

**Complement response form**
- Users can now submit both text and file attachments when responding to a `COMPLEMENT_REQUIS` request.
- `PersonalController::repondreComplement()` validates and stores uploaded files as `DemandeDocument` records with `type = 'complement'`, stored under `demandes/{id}/complements` on the `public` disk.
- Form in `request-details.blade.php` uses `enctype="multipart/form-data"` and accepts PDF, JPG, PNG, Word files (max 5 MB each).

**PDF redesign**
- `resources/views/demandes/pdf/generic.blade.php` fully rewritten with a professional layout: republic header, document-type badge, status bar, annotation box, blue section headers, alternating table rows, documents table with file sizes, two-block signature zone, fixed three-column footer, and a faint diagonal watermark.

**Layout**
- `resources/views/layouts/app.blade.php`: body uses `bg-gray-100`; main wrapper has `border border-gray-200 mx-4 my-4 rounded-lg shadow-sm` for a visible framed card appearance.

**Codebase cleanup**
- Deleted obsolete controllers: `FonctionnaireController`, `InstitutionController`, `PensionnaireController`, `DemandeApiController`.
- Deleted obsolete models: `RequestHistory`, `RequestType`, `Contact`, `ErrorLog`.
- Deleted `app/Helpers/ErrorLoggerService.php`, `app/Enums/RequestEventTypeEnum.php`, `resources/views/enregistrement-pensionnaire/`.

**Architecture diagrams**
- Generated 4 PlantUML files in `documentation/`: `use_case.puml`, `flux.puml`, `sequence.puml`, `entity_association.puml`.

## Session Log — 2026-04-05

### Completed

**Dossier categorization system**
- Added `app/Enums/CategorieDossierEnum.php` — 6 categories: `pension`, `urgent`, `prestations`, `administratif`, `correspondances`, `rencontre`, each with `label()` and `badgeClass()`.
- Added `categorie()` method to `TypeDemandeEnum` mapping every request type to its default `CategorieDossierEnum`.
- Migration `2026_04_05_000001_add_categorie_and_urgence_to_demandes_table.php` adds `categorie` (string, nullable) and `is_urgent` (boolean, default false) to `demandes`.
- `Demande::booted()` auto-classifies on save: `is_urgent` flag overrides type-derived category → `DOSSIERS_URGENTS`. Added `isUrgent()`, `categorieEnum()`, `categorieLabel()` helpers.

**Demande de rencontre (public videoconference request)**
- `DEMANDE_RENCONTRE` added to `TypeDemandeEnum` as a general (non-role-specific) request type.
- New `DemandeRencontreController` (`create` / `store`) — public routes, no auth required, stores directly as a submitted demande.
- New view `resources/views/demandes/rencontre/create.blade.php`.
- Routes: `GET|POST /demande-rencontre` → `demandes.rencontre.{create,store}`.
- Link added to `menubar.blade.php`.

**Corbeille / folder directory improvements**
- Flash success message displayed at top of `corbeille.blade.php`.
- Grid expanded to `xl:grid-cols-6` to accommodate 6 folder categories.
- Added `indigo` color variant to folder color map.
- Folder icon color grays out when count is 0.
- Removed the redundant "Mes demandes" table section from `corbeille` (demandes are accessed via the folder cards).

**Cleanup**
- Deleted `RequestTypeSeeder` (superseded by `TypeDemandeSeeder`) and removed it from `DatabaseSeeder`.
- Deleted obsolete migration `2026_03_29_221916_add_subject_to_contacts_table.php`.
- Simplified `TypeDemandeSeeder`.
- Refactored `PersonalController` (significant size reduction).

### What's Next

- [ ] Write feature tests for the demande workflow (submission → transfer → complement → approval)
- [ ] Add email templates (currently using Laravel default mail layout)
- [ ] Build the admin dashboard with stats (demandes by status, by type, by service)
- [ ] Implement PDF download from the agent management panel
- [ ] Handle file preview/download for attached documents in `request-details`
- [ ] Add pagination or infinite scroll to the messages thread
- [ ] Render the PlantUML diagrams and add them to project documentation
- [ ] Admin view for `DEMANDE_RENCONTRE` — schedule confirmation, accept/decline flow
- [ ] Display `categorie` and `is_urgent` badge in `request-details` and management list views
