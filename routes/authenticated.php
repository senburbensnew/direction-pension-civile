<?php

use App\Http\Controllers\Admin\PensionMandatManagementController;
use App\Http\Controllers\BeneficiaireEspaceController;
use App\Http\Controllers\BeneficiaireRelationDemandeController;
use App\Http\Controllers\CompteProvisoireController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DemandeCreationCompteManagementController;
use App\Http\Controllers\DemandeDocumentController;
use App\Http\Controllers\DemandeManagementController;
use App\Http\Controllers\DemandeMiseAJourController;
use App\Http\Controllers\DemandePdfController;
use App\Http\Controllers\DemandeRencontreController;
use App\Http\Controllers\Formalites\BeneficiaireRelationManagementController;
use App\Http\Controllers\Formalites\BeneficiaireRepresentationHubController;
use App\Http\Controllers\Formalites\BeneficiaireRepresentationPieceController;
use App\Http\Controllers\Formalites\FormaliteSuiviController;
use App\Http\Controllers\Formalites\PensionMandatFormalitesController;
use App\Http\Controllers\MonDemandeCreationCompteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PensionAffiliationController;
use App\Http\Controllers\PensionContextController;
use App\Http\Controllers\PensionMandatController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RdvAgentAvailabilityController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get(
    '/compte/provisoire',
    CompteProvisoireController::class
)->name('compte.provisoire');

Route::get(
    '/mon-compte/demande-creation',
    [MonDemandeCreationCompteController::class, 'show']
)->name('compte.demande.show');

Route::match(
    ['put', 'patch'],
    '/mon-compte/demande-creation',
    [MonDemandeCreationCompteController::class, 'update']
)->name('compte.demande.update');

/*
|--------------------------------------------------------------------------
| Profile (compte actif uniquement)
|--------------------------------------------------------------------------
*/

Route::middleware(['permanent.account', 'pension.context'])->group(function () {
    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile/profile-photo',
        [ProfileController::class, 'updateProfilePhoto']
    )->name('profile.profile-photo.update');

    Route::patch(
        '/compte/contexte-pension',
        [PensionContextController::class, 'update']
    )->name('compte.pension-context.update');

    Route::middleware('password.confirm')->group(function () {
        Route::patch(
            '/profile',
            [ProfileController::class, 'update']
        )->name('profile.update');

        Route::delete(
            '/profile',
            [ProfileController::class, 'destroy']
        )->name('profile.destroy');

        Route::post(
            '/compte/codes-pension',
            [PensionAffiliationController::class, 'store']
        )->name('compte.affiliations.store');

        Route::delete(
            '/compte/codes-pension/{affiliation}',
            [PensionAffiliationController::class, 'destroy']
        )->name('compte.affiliations.destroy');

        Route::patch(
            '/compte/codes-pension/{affiliation}/principal',
            [PensionAffiliationController::class, 'setPrimary']
        )->name('compte.affiliations.set-primary');

        Route::patch(
            '/compte/codes-pension/{affiliation}/description',
            [PensionAffiliationController::class, 'updateDescription']
        )->name('compte.affiliations.update-description');

        Route::post(
            '/compte/mandats',
            [PensionMandatController::class, 'store']
        )->name('compte.mandats.store');
    });

    Route::get(
        '/compte/codes-pension',
        [PensionAffiliationController::class, 'index']
    )->name('compte.affiliations.index');

    Route::get(
        '/compte/mandats',
        [PensionMandatController::class, 'index']
    )->name('compte.mandats.index');

    Route::get(
        '/compte/beneficiaires',
        [BeneficiaireEspaceController::class, 'index']
    )->name('compte.beneficiaires.index');

    Route::get(
        '/compte/beneficiaires/{beneficiaire}/contexte',
        [PensionContextController::class, 'activate']
    )->name('compte.beneficiaires.context');

    Route::get(
        '/compte/relations/demander',
        [BeneficiaireRelationDemandeController::class, 'create']
    )->name('compte.relations.create');

    Route::post(
        '/compte/relations/demander',
        [BeneficiaireRelationDemandeController::class, 'store']
    )->name('compte.relations.store');
});

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
    ->middleware(['not.admin', 'permanent.account', 'pension.context'])
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
        )->middleware('password.confirm')->name('destroy');
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

        Route::middleware('password.confirm')->group(function () {
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
    });

/*
|--------------------------------------------------------------------------
| Workflow reception
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['corbeille.access', 'password.confirm'])
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
    ->middleware('role:'.User::ROLE_AGENT_FORMALITES)
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

        Route::get(
            '/{demandeCreationCompte}/pieces/{media}',
            [
                DemandeCreationCompteManagementController::class,
                'piece',
            ]
        )->name('piece');

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

Route::get(
    '/formalites/representations',
    [BeneficiaireRepresentationHubController::class, 'index']
)->name('formalites.representations.index')
    ->middleware(['role_or_permission:pension_mandats.view|beneficiaire_relations.view']);

Route::get(
    '/formalites/suivi',
    [FormaliteSuiviController::class, 'index']
)->name('formalites.suivi.index')
    ->middleware('role:'.implode('|', FormaliteSuiviController::accessRoles()));

Route::prefix('formalites/pension-mandats')
    ->name('formalites.pension-mandats.')
    ->middleware(['role_or_permission:pension_mandats.view'])
    ->group(function () {
        Route::get('/{mandat}/pieces/{media}', [BeneficiaireRepresentationPieceController::class, 'mandatPiece'])
            ->name('piece');
        Route::get('/', [PensionMandatFormalitesController::class, 'index'])->name('index');
        Route::post('/{mandat}/approve', [PensionMandatManagementController::class, 'approve'])
            ->middleware('role_or_permission:pension_mandats.approuver')
            ->name('approve');
        Route::post('/{mandat}/refuse', [PensionMandatManagementController::class, 'refuse'])
            ->middleware('role_or_permission:pension_mandats.approuver')
            ->name('refuse');
        Route::post('/{mandat}/revoke', [PensionMandatManagementController::class, 'revoke'])
            ->middleware('role_or_permission:pension_mandats.revoquer')
            ->name('revoke');
    });

Route::prefix('formalites/beneficiaire-relations')
    ->name('formalites.beneficiaire-relations.')
    ->middleware(['role_or_permission:admin|beneficiaire_relations.view'])
    ->group(function () {
        Route::get('/demandes/{demande}/pieces/{media}', [BeneficiaireRepresentationPieceController::class, 'relationPiece'])
            ->name('piece');
        Route::get('/', [BeneficiaireRelationManagementController::class, 'index'])->name('index');
        Route::post('/demandes/{demande}/approve', [BeneficiaireRelationManagementController::class, 'approve'])
            ->middleware('role_or_permission:admin|beneficiaire_relations.approuver')
            ->name('approve');
        Route::post('/demandes/{demande}/refuse', [BeneficiaireRelationManagementController::class, 'refuse'])
            ->middleware('role_or_permission:admin|beneficiaire_relations.approuver')
            ->name('refuse');
        Route::post('/links/{link}/revoke', [BeneficiaireRelationManagementController::class, 'revokeLink'])
            ->middleware('role_or_permission:admin|beneficiaire_relations.revoquer')
            ->name('revoke-link');
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
            '/disponibilites',
            [
                RdvAgentAvailabilityController::class,
                'edit',
            ]
        )->name('disponibilites.edit');

        Route::post(
            '/disponibilites',
            [
                RdvAgentAvailabilityController::class,
                'update',
            ]
        )->name('disponibilites.update');

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

        Route::post(
            '/{demande}/reattribuer-agent',
            [
                DemandeRencontreController::class,
                'reattribuerAgent',
            ]
        )->name('reattribuer');
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
        'role:admin|directeur|assistant_directeur',
        'password.confirm',
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
