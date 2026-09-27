<?php

use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CarouselController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactParameterController;
use App\Http\Controllers\ContactSubjectController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DemandeCreationCompteController;
use App\Http\Controllers\DemandeCreationCompteManagementController;
use App\Http\Controllers\DemandeDocumentController;
use App\Http\Controllers\DemandeManagementController;
use App\Http\Controllers\DemandeMiseAJourController;
use App\Http\Controllers\DemandePdfController;
use App\Http\Controllers\DemandeRencontreController;
use App\Http\Controllers\DirectionDepartementaleController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FluxTransitionController;
use App\Http\Controllers\GlossaireController;
use App\Http\Controllers\InstitutionImageController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MediathequeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\OcrTestController;
use App\Http\Controllers\PartenaireController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\QuiSommesNousController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowStepController;
use App\Models\Actualite;
use App\Models\Report;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $latestActualites = Actualite::with('images')
        ->where('published', true)
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get();

    $recentReports = Report::where('status', 'published')
        ->orderBy('published_at', 'desc')
        ->take(3)
        ->get();

    $carousels = \App\Models\Carousel::where(
        'status',
        true
    )
        ->ordered()
        ->get();

    return view(
        'home',
        compact(
            'latestActualites',
            'recentReports',
            'carousels'
        )
    );
})->name('home');

/*
|--------------------------------------------------------------------------
| Static pages
|--------------------------------------------------------------------------
*/

Route::get(
    '/simulateur-calcul',
    fn () => view('fonctionnaire.simulateur-calcul')
)->name('simulateur-calcul');

Route::get(
    '/politique-confidentialite',
    fn () => view('privacy')
)->name('privacy.policy');

Route::get(
    '/conditions-utilisation',
    fn () => view('terms')
)->name('terms.policy');

/*
|--------------------------------------------------------------------------
| Content pages
|--------------------------------------------------------------------------
*/

Route::get(
    '/glossaire',
    [GlossaireController::class, 'publicIndex']
)->name('glossaire');

Route::get(
    '/faq',
    [FaqController::class, 'publicIndex']
)->name('faq.index');

Route::get(
    '/textes_documents_legaux',
    [PublicationController::class, 'publicIndex']
)->name('textes_documents_legaux');

Route::get(
    '/publications/{publication}/download',
    [PublicationController::class, 'download']
)->name('publications.download');

Route::get(
    '/contact',
    [ContactController::class, 'index']
)->name('contact');

Route::post(
    '/contact',
    [ContactController::class, 'store']
)->name('contact.store');

/*
|--------------------------------------------------------------------------
| Account creation request
|--------------------------------------------------------------------------
*/

Route::get(
    '/demande-compte',
    [DemandeCreationCompteController::class, 'create']
)->name('demandes.compte.create');

Route::post(
    '/demande-compte',
    [DemandeCreationCompteController::class, 'store']
)->name('demandes.compte.store');

Route::post(
    '/demande-compte/ocr',
    [DemandeCreationCompteController::class, 'extractOcr']
)
    ->middleware('throttle:20,1')
    ->name('demandes.compte.ocr');

Route::get(
    '/demande-compte/disponibilite',
    [DemandeCreationCompteController::class, 'availability']
)
    ->middleware('throttle:60,1')
    ->name('demandes.compte.disponibilite');

/*
|--------------------------------------------------------------------------
| Services / Directions
|--------------------------------------------------------------------------
*/

Route::get(
    '/services/{service:code}',
    [ServiceController::class, 'publicShow']
)->name('services.show');

Route::get(
    '/directions-departementales/{direction:abbr}',
    [DirectionDepartementaleController::class, 'publicShow']
)->name('directions.show');

/*
|--------------------------------------------------------------------------
| Qui sommes-nous
|--------------------------------------------------------------------------
*/

Route::prefix('quisommesnous')
    ->name('quisommesnous.')
    ->group(function () {
        Route::redirect(
            '/presentation',
            '/quisommesnous/missions'
        );

        Route::redirect(
            '/historique',
            '/quisommesnous/missions'
        );

        Route::get(
            '/mots',
            [QuiSommesNousController::class, 'mots']
        )->name('mots');

        Route::get(
            '/profil',
            [QuiSommesNousController::class, 'profil']
        )->name('profil');

        Route::get(
            '/missions',
            [QuiSommesNousController::class, 'missions']
        )->name('missions');

        Route::get(
            '/structure-organique',
            [QuiSommesNousController::class, 'structureOrganique']
        )->name('structure-organique');

        Route::get(
            '/financement',
            [QuiSommesNousController::class, 'financement']
        )->name('financement');
    });

/*
|--------------------------------------------------------------------------
| Media & content
|--------------------------------------------------------------------------
*/

Route::get(
    '/mediatheque',
    [MediathequeController::class, 'publicIndex']
)->name('mediatheque');

/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
*/

Route::get(
    'rapports',
    [ReportController::class, 'index']
)->name('reports.index');

Route::get(
    'rapports/{report}',
    [ReportController::class, 'show']
)->name('reports.show');

Route::get(
    'rapports/{report}/download',
    [ReportController::class, 'download']
)->name('reports.download');

Route::get(
    '/reports/view/{report}',
    fn (Report $report) => response()->file(
        storage_path(
            'app/public/' . $report->file_path
        )
    )
)->name('reports.view');

/*
|--------------------------------------------------------------------------
| Actualités
|--------------------------------------------------------------------------
*/

Route::get(
    'actualites',
    [ActualiteController::class, 'index']
)->name('actualites.index');

Route::get(
    'actualites/{actualite}',
    [ActualiteController::class, 'show']
)->name('actualites.show');

Route::get(
    'actualites/{actualite}/download',
    [ActualiteController::class, 'download']
)->name('actualites.download');

/*
|--------------------------------------------------------------------------
| RENDEZ-VOUS
|--------------------------------------------------------------------------
|
| Règle métier :
|
| Motif
|   ↓
| Service responsable
|   ↓
| Agents du service
|   ↓
| Agent disponible
|   ↓
| Rendez-vous
|
| Les routes ne déterminent ni le service ni l'agent.
| Cette logique appartient à RencontreMotifService,
| RencontreAvailabilityService et RencontreWorkflowService.
|
*/

/*
|--------------------------------------------------------------------------
| Prise de rendez-vous par le pensionné
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Espace pensionné
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:pensionne',
])->group(function () {

    Route::get(
        '/demande-rencontre',
        [DemandeRencontreController::class, 'create']
    )->name('demandes.rencontre.create');

    Route::post(
        '/demande-rencontre',
        [DemandeRencontreController::class, 'store']
    )->name('demandes.rencontre.store');

    /*
    |--------------------------------------------------------------------------
    | Rendez-vous présentiel
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/rendez-vous-physique',
        [DemandeRencontreController::class, 'createPhysique']
    )->name('demandes.rencontre.physique');
});

/*
|--------------------------------------------------------------------------
| Annulation — pensionné, agent_rdv, admin, direction
|--------------------------------------------------------------------------
|
| L'autorisation fine est faite dans le contrôleur via canCancelDemande().
|
*/

Route::middleware(['auth'])->group(function () {

    Route::post(
        '/demande-rencontre/{demande}/annuler',
        [DemandeRencontreController::class, 'annuler']
    )->name('demandes.rencontre.annuler');

    Route::post(
        '/demande-rencontre/{demande}/refuser',
        [DemandeRencontreController::class, 'refuser']
    )->name('demandes.rencontre.refuser');

    Route::post(
        '/demande-rencontre/{demande}/accepter',
        [DemandeRencontreController::class, 'accepter']
    )->name('demandes.rencontre.accepter');
});

/*
|--------------------------------------------------------------------------
| Visioconférence
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get(
        '/rendez-vous/visio/{token}',
        [DemandeRencontreController::class, 'visio']
    )
        ->where(
            'token',
            '[A-Fa-f0-9]{64}'
        )
        ->name('demandes.rencontre.visio');
});

/*
|--------------------------------------------------------------------------
| Newsletter
|--------------------------------------------------------------------------
*/

Route::post(
    '/newsletter/souscription',
    [NewsletterController::class, 'souscription']
)->name('newsletter.souscription');

Route::get(
    '/newsletter/unsubscribe/{token}',
    [NewsletterController::class, 'unsubscribe']
)->name('newsletter.unsubscribe');

/*
|--------------------------------------------------------------------------
| Locale
|--------------------------------------------------------------------------
*/

Route::get(
    '/locale/{locale}',
    [LocaleController::class, 'switch']
)->name('locale');

/*
|--------------------------------------------------------------------------
| Documents
|--------------------------------------------------------------------------
*/

Route::get(
    '/documents/{filename}',
    function ($filename) {
        $path = storage_path(
            "app/public/documents/$filename"
        );

        abort_unless(
            file_exists($path),
            404
        );

        return response()->file($path);
    }
)->name('documents.view');

Route::get(
    '/documents/download/{filename}',
    function ($filename) {
        $path = storage_path(
            "app/public/documents/$filename"
        );

        abort_unless(
            file_exists($path),
            404
        );

        return response()->download($path);
    }
)->name('documents.download');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::patch(
        '/profile/profile-photo',
        [ProfileController::class, 'updateProfilePhoto']
    )->name('profile.profile-photo.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Personal dashboard
    |--------------------------------------------------------------------------
    */

    Route::prefix('personal')
        ->name('personal.')
        ->middleware('not.admin')
        ->group(function () {
            Route::get(
                '/',
                [PersonalController::class, 'index']
            )->name('index');

            Route::get(
                '/dashboard',
                [PersonalController::class, 'dashboard']
            )->name('dashboard');

            Route::get(
                '/requestsDashboard',
                [PersonalController::class, 'requestsDashboard']
            )->name('requests-dashboard');

            Route::get(
                '/dashboard-corbeille',
                [PersonalController::class, 'requestsDashboardCorbeille']
            )->name('requests-dashboard-corbeille');

            Route::middleware('corbeille.access')
                ->group(function () {
                    Route::get(
                        '/corbeille',
                        [PersonalController::class, 'corbeille']
                    )->name('cart');

                    Route::get(
                        '/corbeille/folder',
                        [PersonalController::class, 'corbeilleByFolder']
                    )->name('cart.folder');
                });

            Route::prefix('request')->group(function () {
                Route::get(
                    '/{id}',
                    [PersonalController::class, 'showRequest']
                )->name('request.show');

                Route::get(
                    '/auth/{id}',
                    [
                        PersonalController::class,
                        'showRequestForAuthenticatedUser',
                    ]
                )->name(
                    'request.authenticated-user-request.show'
                );
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Demandes — création
    |--------------------------------------------------------------------------
    */

    Route::prefix('demandes')
        ->name('demandes.')
        ->middleware([
            'not.admin',
            'account.full',
        ])
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Pensionné
            |--------------------------------------------------------------------------
            */

            Route::prefix('virements')
                ->name('virements.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createDemandeVirement'
                    )->name('create');

                    Route::post(
                        '/',
                        'storeDemandeVirement'
                    )->name('store');
                });

            Route::prefix('attestations')
                ->name('attestations.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createDemandeAttestation'
                    )->name('create');

                    Route::post(
                        '/',
                        'storeDemandeAttestation'
                    )->name('store');
                });

            Route::prefix('transfert-cheque')
                ->name('transfert-cheque.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createDemandeTransfertCheque'
                    )->name('create');

                    Route::post(
                        '/',
                        'storeDemandeTransfertCheque'
                    )->name('store');
                });

            Route::prefix('arret-paiement')
                ->name('arret-paiement.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createDemandeArretPaiement'
                    )->name('create');

                    Route::post(
                        '/',
                        'storeDemandeArretPaiement'
                    )->name('store');
                });

            Route::prefix('demande-reinsertion')
                ->name('demande-reinsertion.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createDemandeReinsertion'
                    )->name('create');

                    Route::post(
                        '/',
                        'storeDemandeReinsertion'
                    )->name('store');
                });

            Route::prefix('demande-arret-virement')
                ->name('demande-arret-virement.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createDemandeArretVirement'
                    )->name('create');

                    Route::post(
                        '/',
                        'storeDemandeArretVirement'
                    )->name('store');
                });

            Route::prefix('preuve-existence')
                ->name('preuve-existence.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createPreuveExistence'
                    )->name('create');

                    Route::post(
                        '/',
                        'storePreuveExistence'
                    )->name('store');
                });

            Route::prefix('mise-a-jour')
                ->name('mise-a-jour.')
                ->controller(DemandeMiseAJourController::class)
                ->group(function () {
                    Route::get(
                        '/create',
                        'create'
                    )->name('create');

                    Route::post(
                        '/',
                        'store'
                    )->name('store');
                });

            Route::prefix('pension-pensionne')
                ->name('pension-pensionne.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createDemandePensionPensionne'
                    )->name('create');

                    Route::post(
                        '/',
                        'storeDemandePensionPensionne'
                    )->name('store');
                });

            /*
            |--------------------------------------------------------------------------
            | Fonctionnaire
            |--------------------------------------------------------------------------
            */

            Route::prefix('demande-etat-carriere')
                ->name('demande-etat-carriere.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createDemandeEtatCarriere'
                    )->name('create');

                    Route::post(
                        '/',
                        'storeDemandeEtatCarriere'
                    )->name('store');
                });

            /*
            |--------------------------------------------------------------------------
            | Institution
            |--------------------------------------------------------------------------
            */

            Route::prefix('demande-adhesion')
                ->name('demande-adhesion.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createDemandeAdhesion'
                    )->name('create');

                    Route::post(
                        '/',
                        'storeDemandeAdhesion'
                    )->name('store');
                });

            /*
            |--------------------------------------------------------------------------
            | Pension
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/demande-pension',
                [
                    DemandeController::class,
                    'showDemandesPensionPage',
                ]
            )->name('demande-pension.index');

            Route::prefix('demande-pension-standard')
                ->name('demande-pension-standard.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createDemandePensionStandard'
                    )->name('create');

                    Route::post(
                        '/',
                        'storeDemandePensionStandard'
                    )->name('store');
                });

            Route::prefix('demande-pension-reversion')
                ->name('demande-pension-reversion.')
                ->controller(DemandeController::class)
                ->group(function () {
                    Route::get(
                        '/create/{demandeId?}',
                        'createDemandePensionReversion'
                    )->name('create');

                    Route::post(
                        '/',
                        'storeDemandePensionReversion'
                    )->name('store');
                });

            Route::delete(
                '/{demande}',
                [
                    DemandeController::class,
                    'destroyDemande',
                ]
            )->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Demande actions
    |--------------------------------------------------------------------------
    */

    Route::prefix('demandes')
        ->middleware('not.admin')
        ->group(function () {

            Route::post(
                '/{demande}/documents',
                [
                    DemandeDocumentController::class,
                    'store',
                ]
            )->name('demandedocument.store');

            Route::delete(
                '/documents/{media}',
                [
                    DemandeDocumentController::class,
                    'destroy',
                ]
            )->name('demandedocument.destroy');

            Route::get(
                '/{demande}/pdf',
                [
                    DemandePdfController::class,
                    'download',
                ]
            )->name('demande.pdf');

            Route::get(
                '/{demande}/print',
                [
                    DemandePdfController::class,
                    'print',
                ]
            )->name('demande.print');

            Route::post(
                '/{demande}/annotation',
                [
                    DemandeManagementController::class,
                    'annotate',
                ]
            )->name('demande.annotate');

            Route::post(
                '/{demande}/complement',
                [
                    DemandeManagementController::class,
                    'requestComplement',
                ]
            )->name('demande.complement');

            Route::post(
                '/{demande}/repondre-complement',
                [
                    PersonalController::class,
                    'repondreComplement',
                ]
            )->name('demande.repondre-complement');

            Route::post(
                '/transfert',
                [
                    DemandeManagementController::class,
                    'transfererDemande',
                ]
            )->name('demande.transfert');
        });

    /*
    |--------------------------------------------------------------------------
    | Workflow reception
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('corbeille.access')
        ->group(function () {

            Route::post(
                '/interactions/{interaction}/accepter',
                [
                    DemandeManagementController::class,
                    'accepterReception',
                ]
            )->name('interactions.accepter');

            Route::post(
                '/interactions/{interaction}/refuser',
                [
                    DemandeManagementController::class,
                    'refuserReception',
                ]
            )->name('interactions.refuser');

            Route::post(
                '/demandes/{demande}/affecter',
                [
                    DemandeManagementController::class,
                    'affecterServices',
                ]
            )->name('demandes.affecter');

            Route::post(
                '/interactions/{interaction}/repondre',
                [
                    DemandeManagementController::class,
                    'repondreAffectation',
                ]
            )->name('interactions.repondre');

            Route::post(
                '/demandes/{demande}/assigner-agent',
                [
                    DemandeManagementController::class,
                    'assignerAgent',
                ]
            )->name('demandes.assigner-agent');
        });

    /*
    |--------------------------------------------------------------------------
    | Account creation requests — Formalités
    |--------------------------------------------------------------------------
    */

    Route::prefix('formalites/comptes-demandes')
        ->name('formalites.comptes-demandes.')
        ->middleware('role:agent_rdv')
        ->group(function () {

            Route::get(
                '/',
                [
                    DemandeCreationCompteManagementController::class,
                    'index',
                ]
            )->name('index');

            Route::get(
                '/{demandeCreationCompte}',
                [
                    DemandeCreationCompteManagementController::class,
                    'show',
                ]
            )->name('show');

            Route::post(
                '/{demandeCreationCompte}/accepter',
                [
                    DemandeCreationCompteManagementController::class,
                    'accepter',
                ]
            )->name('accepter');

            Route::post(
                '/{demandeCreationCompte}/refuser',
                [
                    DemandeCreationCompteManagementController::class,
                    'refuser',
                ]
            )->name('refuser');
        });

    /*
    |--------------------------------------------------------------------------
    | Pilotage des rendez-vous
    |--------------------------------------------------------------------------
    |
    | Les agents voient les demandes selon leur accès à la corbeille.
    | Le contrôleur détermine ensuite le service responsable et
    | l'agent de rendez-vous.
    |
    */

    Route::prefix('pilotage-rendez-vous')
        ->name('rencontres.pilotage.')
        ->middleware('corbeille.access')
        ->group(function () {

            Route::get(
                '/',
                [
                    DemandeRencontreController::class,
                    'index',
                ]
            )->name('index');

            Route::get(
                '/{demande}',
                [
                    DemandeRencontreController::class,
                    'show',
                ]
            )->name('show');

            Route::post(
                '/{demande}/examiner',
                [
                    DemandeRencontreController::class,
                    'examiner',
                ]
            )->name('examiner');

            Route::post(
                '/{demande}/attribuer',
                [
                    DemandeRencontreController::class,
                    'attribuer',
                ]
            )->name('attribuer');

            Route::post(
                '/{demande}/proposer-creneau',
                [
                    DemandeRencontreController::class,
                    'proposerCreneau',
                ]
            )->name('proposer-creneau');

            Route::post(
                '/{demande}/accepter',
                [
                    DemandeRencontreController::class,
                    'accepter',
                ]
            )->name('accepter');

            Route::post(
                '/{demande}/refuser',
                [
                    DemandeRencontreController::class,
                    'refuser',
                ]
            )->name('refuser');

            Route::post(
                '/{demande}/clore',
                [
                    DemandeRencontreController::class,
                    'clore',
                ]
            )->name('clore');

            /*
            |--------------------------------------------------------------------------
            | Réorientation administrative
            |--------------------------------------------------------------------------
            |
            | Une réorientation est volontaire et administrative.
            | Elle ne fait pas partie de la détermination automatique
            | initiale du service par motif.
            |
            */

            Route::post(
                '/{demande}/reorienter',
                [
                    DemandeRencontreController::class,
                    'reorienter',
                ]
            )->name('reorienter');

            Route::post(
                '/{demande}/suite',
                [
                    DemandeRencontreController::class,
                    'enregistrerSuite',
                ]
            )->name('suite');

            Route::post(
                '/{demande}/confirmer-identite',
                [
                    DemandeRencontreController::class,
                    'confirmerIdentite',
                ]
            )->name('confirmer-identite');
        });

    /*
    |--------------------------------------------------------------------------
    | Décision finale — Direction ou admin
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware([
            'auth',
            'role:admin|direction|directeur|assistant_directeur',
        ])
        ->group(function () {

            Route::post(
                '/demandes/{demande}/approuver',
                [
                    DemandeManagementController::class,
                    'approuver',
                ]
            )->name('demandes.approuver');

            Route::post(
                '/demandes/{demande}/cloturer',
                [
                    DemandeManagementController::class,
                    'cloturer',
                ]
            )->name('demandes.cloturer');

            Route::post(
                '/demandes/{demande}/rejeter',
                [
                    DemandeManagementController::class,
                    'rejeter',
                ]
            )->name('demandes.rejeter');

            Route::post(
                '/demandes/{demande}/rouvrir',
                [
                    DemandeManagementController::class,
                    'rouvrir',
                ]
            )->name('demandes.rouvrir');
        });

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::prefix('notifications')
        ->name('notifications.')
        ->middleware('not.admin')
        ->group(function () {

            Route::get(
                '/',
                [NotificationController::class, 'index']
            )->name('index');

            Route::post(
                '/{id}/mark-read',
                [NotificationController::class, 'markAsRead']
            )->name('markAsRead');

            Route::get(
                '/{id}/open',
                [NotificationController::class, 'open']
            )->name('open');

            Route::post(
                '/mark-all-read',
                [NotificationController::class, 'markAllAsRead']
            )->name('markAllAsRead');

            Route::delete(
                '/{id}',
                [NotificationController::class, 'destroy']
            )->name('destroy');
        });
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth',
        'role:admin',
    ])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [AdminDashboardController::class, 'index']
        )->name('dashboard.index');

        /*
        |--------------------------------------------------------------------------
        | Settings & development tools
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/settings',
            [AdminController::class, 'settings']
        )->name('settings');

        Route::get(
            '/ocr-test',
            [OcrTestController::class, 'create']
        )->name('ocr.test');

        Route::post(
            '/ocr-test',
            [OcrTestController::class, 'store']
        )->name('ocr.test.store');

        Route::post(
            '/toggle-maintenance',
            function () {
                $current = \Illuminate\Support\Facades\DB::table(
                    'parameters'
                )
                    ->where(
                        'name',
                        'is_maintenance_mode'
                    )
                    ->value('value');

                $next =
                    $current === 'true'
                        ? 'false'
                        : 'true';

                \Illuminate\Support\Facades\DB::table(
                    'parameters'
                )
                    ->where(
                        'name',
                        'is_maintenance_mode'
                    )
                    ->update([
                        'value' => $next,
                    ]);

                \Illuminate\Support\Facades\Cache::forget(
                    'is_maintenance_mode'
                );

                $label =
                    $next === 'true'
                        ? 'activé'
                        : 'désactivé';

                return redirect()
                    ->back()
                    ->with(
                        'success',
                        "Mode maintenance {$label}."
                    );
            }
        )->name('toggle.maintenance');

        /*
        |--------------------------------------------------------------------------
        | Account creation requests
        |--------------------------------------------------------------------------
        */

        Route::prefix('comptes-demandes')
            ->name('comptes-demandes.')
            ->group(function () {

                Route::get(
                    '/',
                    [
                        DemandeCreationCompteManagementController::class,
                        'index',
                    ]
                )->name('index');

                Route::get(
                    '/{demandeCreationCompte}',
                    [
                        DemandeCreationCompteManagementController::class,
                        'show',
                    ]
                )->name('show');
            });

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'users',
            UserController::class
        );

        Route::patch(
            'users/{user}/toggle-active',
            [
                UserController::class,
                'toggleActive',
            ]
        )->name('users.toggle-active');

        /*
        |--------------------------------------------------------------------------
        | Carousels / Posts
        |--------------------------------------------------------------------------
        */

        Route::post(
            'carousels/reorder',
            [
                CarouselController::class,
                'reorder',
            ]
        )->name('carousels.reorder');

        Route::resource(
            'carousels',
            CarouselController::class
        );

        Route::resource(
            'posts',
            PostController::class
        );

        /*
        |--------------------------------------------------------------------------
        | Services / Roles / Permissions
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/services',
            [ServiceController::class, 'index']
        )->name('services.index');

        Route::get(
            '/roles',
            [RoleController::class, 'index']
        )->name('roles.index');

        Route::get(
            '/permissions',
            [PermissionController::class, 'index']
        )->name('permissions.index');

        /*
        |--------------------------------------------------------------------------
        | Workflow
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/flux-transitions',
            [FluxTransitionController::class, 'index']
        )->name('flux-transitions.index');

        Route::post(
            '/flux-transitions/types',
            [FluxTransitionController::class, 'storeType']
        )->name('flux-transitions.types.store');

        Route::delete(
            '/flux-transitions/types/{typeCode}',
            [FluxTransitionController::class, 'destroyType']
        )
            ->where(
                'typeCode',
                '[A-Z0-9_]+'
            )
            ->name('flux-transitions.types.destroy');

        Route::post(
            '/flux-transitions/step-transitions',
            [FluxTransitionController::class, 'storeStepTransition']
        )->name(
            'flux-transitions.step-transitions.store'
        );

        Route::patch(
            '/flux-transitions/step-transitions/{stepTransition}',
            [FluxTransitionController::class, 'updateStepTransition']
        )->name(
            'flux-transitions.step-transitions.update'
        );

        Route::delete(
            '/flux-transitions/step-transitions/{stepTransition}',
            [FluxTransitionController::class, 'destroyStepTransition']
        )->name(
            'flux-transitions.step-transitions.destroy'
        );

        Route::post(
            '/flux-transitions/step-transitions/{stepTransition}/move-up',
            [FluxTransitionController::class, 'moveUpStepTransition']
        )->name(
            'flux-transitions.step-transitions.move-up'
        );

        Route::post(
            '/flux-transitions/step-transitions/{stepTransition}/move-down',
            [FluxTransitionController::class, 'moveDownStepTransition']
        )->name(
            'flux-transitions.step-transitions.move-down'
        );

        /*
        |--------------------------------------------------------------------------
        | Workflow steps
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/workflow-steps',
            [WorkflowStepController::class, 'store']
        )->name('workflow-steps.store');

        Route::post(
            '/workflow-steps/{workflowStep}/clone',
            [WorkflowStepController::class, 'clone']
        )->name('workflow-steps.clone');

        Route::patch(
            '/workflow-steps/{workflowStep}',
            [WorkflowStepController::class, 'update']
        )->name('workflow-steps.update');

        Route::delete(
            '/workflow-steps/{workflowStep}',
            [WorkflowStepController::class, 'destroy']
        )->name('workflow-steps.destroy');

        /*
        |--------------------------------------------------------------------------
        | Required circuit services
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/flux-transitions/required',
            [FluxTransitionController::class, 'storeRequired']
        )->name('flux-transitions.required.store');

        Route::delete(
            '/flux-transitions/required/{requiredCircuitService}',
            [FluxTransitionController::class, 'destroyRequired']
        )->name('flux-transitions.required.destroy');

        /*
        |--------------------------------------------------------------------------
        | SLA
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/flux-transitions/sla',
            [FluxTransitionController::class, 'storeSla']
        )->name('flux-transitions.sla.store');

        Route::delete(
            '/flux-transitions/sla/{serviceSla}',
            [FluxTransitionController::class, 'destroySla']
        )->name('flux-transitions.sla.destroy');

        /*
        |--------------------------------------------------------------------------
        | Documents
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/documents/upload',
            [DocumentController::class, 'index']
        )->name('documents.index');

        Route::post(
            '/documents/upload',
            [DocumentController::class, 'upload']
        )->name('documents.upload');

        /*
        |--------------------------------------------------------------------------
        | Demande management
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/demandes',
            [DemandeManagementController::class, 'index']
        )->name('demandes.index');

        Route::get(
            '/demandes/{demande}',
            [DemandeManagementController::class, 'edit']
        )->name('demandes.show');

        Route::post(
            '/demandes/{demande}/update-status',
            [DemandeManagementController::class, 'updateStatus']
        )->name('demandes.updateStatus');

        Route::post(
            '/demandes/{demande}/annuler',
            [DemandeManagementController::class, 'annuler']
        )->name('demandes.annuler');

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::get(
            'rapports',
            [ReportController::class, 'adminIndex']
        )->name('reports.admin.index');

        Route::get(
            'rapports/create',
            [ReportController::class, 'create']
        )->name('reports.create');

        Route::post(
            'rapports',
            [ReportController::class, 'store']
        )->name('reports.store');

        Route::get(
            'rapports/{report}/edit',
            [ReportController::class, 'edit']
        )->name('reports.edit');

        Route::put(
            'rapports/{report}',
            [ReportController::class, 'update']
        )->name('reports.update');

        Route::delete(
            'rapports/{report}',
            [ReportController::class, 'destroy']
        )->name('reports.destroy');

        Route::post(
            'rapports/{report}/toggle',
            [ReportController::class, 'togglePublish']
        )->name('reports.toggle');

        /*
        |--------------------------------------------------------------------------
        | Actualités
        |--------------------------------------------------------------------------
        */

        Route::get(
            'actualites',
            [ActualiteController::class, 'adminIndex']
        )->name('actualites.admin.index');

        Route::get(
            'actualites/create',
            [ActualiteController::class, 'create']
        )->name('actualites.create');

        Route::post(
            'actualites',
            [ActualiteController::class, 'store']
        )->name('actualites.store');

        Route::get(
            'actualites/{actualite}/edit',
            [ActualiteController::class, 'edit']
        )->name('actualites.edit');

        Route::put(
            'actualites/{actualite}',
            [ActualiteController::class, 'update']
        )->name('actualites.update');

        Route::delete(
            'actualites/{actualite}',
            [ActualiteController::class, 'destroy']
        )->name('actualites.destroy');

        Route::post(
            'actualites/{actualite}/toggle',
            [ActualiteController::class, 'togglePublish']
        )->name('actualites.toggle');

        /*
        |--------------------------------------------------------------------------
        | Newsletter
        |--------------------------------------------------------------------------
        */

        Route::get(
            'newsletter',
            [NewsletterController::class, 'adminIndex']
        )->name('newsletter.admin.index');

        Route::get(
            'newsletter/compose',
            [NewsletterController::class, 'compose']
        )->name('newsletter.compose');

        Route::post(
            'newsletter/send',
            [NewsletterController::class, 'send']
        )->name('newsletter.send');

        Route::get(
            'newsletter/export',
            [NewsletterController::class, 'export']
        )->name('newsletter.export');

        Route::delete(
            'newsletter/{newsletter}',
            [NewsletterController::class, 'destroy']
        )->name('newsletter.destroy');

        Route::delete(
            'newsletter/campaigns/{campaign}',
            [NewsletterController::class, 'destroyCampaign']
        )->name('newsletter.campaigns.destroy');

        /*
        |--------------------------------------------------------------------------
        | Contact
        |--------------------------------------------------------------------------
        */

        Route::get(
            'contacts',
            [ContactController::class, 'adminIndex']
        )->name('contacts.index');

        Route::get(
            'contacts/{contact}',
            [ContactController::class, 'adminShow']
        )->name('contacts.show');

        Route::post(
            'contacts/{contact}/read',
            [ContactController::class, 'markRead']
        )->name('contacts.markRead');

        Route::post(
            'contacts/{contact}/unread',
            [ContactController::class, 'markUnread']
        )->name('contacts.markUnread');

        Route::post(
            'contacts/mark-all-read',
            [ContactController::class, 'markAllRead']
        )->name('contacts.markAllRead');

        Route::delete(
            'contacts/{contact}',
            [ContactController::class, 'adminDestroy']
        )->name('contacts.destroy');

        /*
        |--------------------------------------------------------------------------
        | FAQ
        |--------------------------------------------------------------------------
        */

        Route::get(
            'faq',
            [FaqController::class, 'adminIndex']
        )->name('faq.index');

        Route::post(
            'faq',
            [FaqController::class, 'store']
        )->name('faq.store');

        Route::put(
            'faq/{faqItem}',
            [FaqController::class, 'update']
        )->name('faq.update');

        Route::delete(
            'faq/{faqItem}',
            [FaqController::class, 'destroy']
        )->name('faq.destroy');

        Route::post(
            'faq/{faqItem}/toggle',
            [FaqController::class, 'togglePublish']
        )->name('faq.toggle');

        /*
        |--------------------------------------------------------------------------
        | Glossaire
        |--------------------------------------------------------------------------
        */

        Route::get(
            'glossaire',
            [GlossaireController::class, 'adminIndex']
        )->name('glossaire.index');

        Route::post(
            'glossaire',
            [GlossaireController::class, 'store']
        )->name('glossaire.store');

        Route::put(
            'glossaire/{glossaireTerm}',
            [GlossaireController::class, 'update']
        )->name('glossaire.update');

        Route::delete(
            'glossaire/{glossaireTerm}',
            [GlossaireController::class, 'destroy']
        )->name('glossaire.destroy');

        Route::post(
            'glossaire/{glossaireTerm}/toggle',
            [GlossaireController::class, 'togglePublish']
        )->name('glossaire.toggle');

        /*
        |--------------------------------------------------------------------------
        | Publications
        |--------------------------------------------------------------------------
        */

        Route::get(
            'publications',
            [PublicationController::class, 'adminIndex']
        )->name('publications.index');

        Route::post(
            'publications',
            [PublicationController::class, 'store']
        )->name('publications.store');

        Route::put(
            'publications/{publication}',
            [PublicationController::class, 'update']
        )->name('publications.update');

        Route::delete(
            'publications/{publication}',
            [PublicationController::class, 'destroy']
        )->name('publications.destroy');

        Route::post(
            'publications/{publication}/toggle',
            [PublicationController::class, 'togglePublish']
        )->name('publications.toggle');

        Route::post(
            'publication-types',
            [PublicationController::class, 'storeType']
        )->name('publication-types.store');

        Route::put(
            'publication-types/{publicationType}',
            [PublicationController::class, 'updateType']
        )->name('publication-types.update');

        Route::delete(
            'publication-types/{publicationType}',
            [PublicationController::class, 'destroyType']
        )->name('publication-types.destroy');

        /*
        |--------------------------------------------------------------------------
        | Médiathèque
        |--------------------------------------------------------------------------
        */

        Route::get(
            'mediatheque',
            [MediathequeController::class, 'adminIndex']
        )->name('mediatheque.index');

        Route::post(
            'mediatheque',
            [MediathequeController::class, 'store']
        )->name('mediatheque.store');

        Route::put(
            'mediatheque/{mediathequeItem}',
            [MediathequeController::class, 'update']
        )->name('mediatheque.update');

        Route::delete(
            'mediatheque/{mediathequeItem}',
            [MediathequeController::class, 'destroy']
        )->name('mediatheque.destroy');

        Route::post(
            'mediatheque/{mediathequeItem}/toggle',
            [MediathequeController::class, 'togglePublish']
        )->name('mediatheque.toggle');

        /*
        |--------------------------------------------------------------------------
        | Institution en images
        |--------------------------------------------------------------------------
        */

        Route::get(
            'institution-images',
            [InstitutionImageController::class, 'index']
        )->name('institution-images.index');

        Route::get(
            'institution-images/create',
            [InstitutionImageController::class, 'create']
        )->name('institution-images.create');

        Route::post(
            'institution-images',
            [InstitutionImageController::class, 'store']
        )->name('institution-images.store');

        Route::get(
            'institution-images/{institutionImage}/edit',
            [InstitutionImageController::class, 'edit']
        )->name('institution-images.edit');

        Route::put(
            'institution-images/{institutionImage}',
            [InstitutionImageController::class, 'update']
        )->name('institution-images.update');

        Route::delete(
            'institution-images/{institutionImage}',
            [InstitutionImageController::class, 'destroy']
        )->name('institution-images.destroy');

        Route::post(
            'institution-images/reorder',
            [InstitutionImageController::class, 'reorder']
        )->name('institution-images.reorder');

        /*
        |--------------------------------------------------------------------------
        | Officiels
        |--------------------------------------------------------------------------
        */

        Route::get(
            'officiels',
            [OfficialController::class, 'index']
        )->name('officials.index');

        Route::get(
            'officiels/create',
            [OfficialController::class, 'create']
        )->name('officials.create');

        Route::post(
            'officiels',
            [OfficialController::class, 'store']
        )->name('officials.store');

        Route::get(
            'officiels/{official}/edit',
            [OfficialController::class, 'edit']
        )->name('officials.edit');

        Route::put(
            'officiels/{official}',
            [OfficialController::class, 'update']
        )->name('officials.update');

        Route::delete(
            'officiels/{official}',
            [OfficialController::class, 'destroy']
        )->name('officials.destroy');

        /*
        |--------------------------------------------------------------------------
        | Directions départementales
        |--------------------------------------------------------------------------
        */

        Route::get(
            'directions',
            [DirectionDepartementaleController::class,
                'index']
        )->name('directions.index');

        Route::post(
            'directions',
            [DirectionDepartementaleController::class,
                'store']
        )->name('directions.store');

        Route::put(
            'directions/{direction}',
            [DirectionDepartementaleController::class,
                'update']
        )->name('directions.update');

        Route::delete(
            'directions/{direction}',
            [DirectionDepartementaleController::class,
                'destroy']
        )->name('directions.destroy');

        /*
        |--------------------------------------------------------------------------
        | Contact parameters
        |--------------------------------------------------------------------------
        */

        Route::get(
            'contact-parameters',
            [ContactParameterController::class, 'index']
        )->name('contact-parameters.index');

        Route::put(
            'contact-parameters',
            [ContactParameterController::class, 'update']
        )->name('contact-parameters.update');

        Route::post(
            'contact-subjects',
            [ContactSubjectController::class, 'store']
        )->name('contact-subjects.store');

        Route::put(
            'contact-subjects/{contactSubject}',
            [ContactSubjectController::class, 'update']
        )->name('contact-subjects.update');

        Route::delete(
            'contact-subjects/{contactSubject}',
            [ContactSubjectController::class, 'destroy']
        )->name('contact-subjects.destroy');

        Route::post(
            'contact-subjects/{contactSubject}/toggle',
            [ContactSubjectController::class, 'toggle']
        )->name('contact-subjects.toggle');

        /*
        |--------------------------------------------------------------------------
        | Partenaires
        |--------------------------------------------------------------------------
        */

        Route::get(
            'partenaires',
            [PartenaireController::class, 'index']
        )->name('partenaires.index');

        Route::get(
            'partenaires/create',
            [PartenaireController::class, 'create']
        )->name('partenaires.create');

        Route::post(
            'partenaires',
            [PartenaireController::class, 'store']
        )->name('partenaires.store');

        Route::get(
            'partenaires/{partenaire}/edit',
            [PartenaireController::class, 'edit']
        )->name('partenaires.edit');

        Route::put(
            'partenaires/{partenaire}',
            [PartenaireController::class, 'update']
        )->name('partenaires.update');

        Route::delete(
            'partenaires/{partenaire}',
            [PartenaireController::class, 'destroy']
        )->name('partenaires.destroy');

        Route::post(
            'partenaires/reorder',
            [PartenaireController::class, 'reorder']
        )->name('partenaires.reorder');
    });

require __DIR__ . '/auth.php';