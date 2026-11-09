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

/*
|--------------------------------------------------------------------------
| AUTHENTICATED APP ROUTES
|--------------------------------------------------------------------------
| Included from routes/web.php (middleware web, auth, account.permanent).
|--------------------------------------------------------------------------
*/

Route::get('/compte/provisoire', CompteProvisoireController::class)->name('compte.provisoire');

Route::prefix('mon-compte/demande-creation')->name('compte.demande.')->controller(MonDemandeCreationCompteController::class)->group(function () {
    Route::get('/', 'show')->name('show');
    Route::match(['put', 'patch'], '/', 'update')->name('update');
});

Route::middleware(['permanent.account', 'pension.context'])->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile/profile-photo', 'updateProfilePhoto')->name('profile.profile-photo.update');
    });

    Route::patch('/compte/contexte-pension', [PensionContextController::class, 'update'])->name('compte.pension-context.update');

    Route::middleware('password.confirm')->group(function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::patch('/profile', 'update')->name('profile.update');
            Route::delete('/profile', 'destroy')->name('profile.destroy');
        });

        Route::prefix('compte')->name('compte.')->group(function () {
            Route::controller(PensionAffiliationController::class)->group(function () {
                Route::post('/codes-pension', 'store')->name('affiliations.store');
                Route::delete('/codes-pension/{affiliation}', 'destroy')->name('affiliations.destroy');
                Route::patch('/codes-pension/{affiliation}/principal', 'setPrimary')->name('affiliations.set-primary');
                Route::patch('/codes-pension/{affiliation}/description', 'updateDescription')->name('affiliations.update-description');
            });

            Route::post('/mandats', [PensionMandatController::class, 'store'])->name('mandats.store');
        });
    });

    Route::get('/compte/codes-pension', [PensionAffiliationController::class, 'index'])->name('compte.affiliations.index');
    Route::get('/compte/mandats', [PensionMandatController::class, 'index'])->name('compte.mandats.index');
    Route::get('/compte/beneficiaires', [BeneficiaireEspaceController::class, 'index'])->name('compte.beneficiaires.index');
    Route::get('/compte/beneficiaires/{beneficiaire}/contexte', [PensionContextController::class, 'activate'])->name('compte.beneficiaires.context');

    Route::prefix('compte/relations')->name('compte.relations.')->controller(BeneficiaireRelationDemandeController::class)->group(function () {
        Route::get('/demander', 'create')->name('create');
        Route::post('/demander', 'store')->name('store');
    });
});

Route::prefix('personal')->name('personal.')->middleware('not.admin')->controller(PersonalController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/requestsDashboard', 'requestsDashboard')->name('requests-dashboard');
    Route::get('/dashboard-corbeille', 'requestsDashboardCorbeille')->name('requests-dashboard-corbeille');

    Route::middleware('corbeille.access')->group(function () {
        Route::get('/corbeille', 'corbeille')->name('cart');
        Route::get('/corbeille/folder', 'corbeilleByFolder')->name('cart.folder');
    });

    Route::prefix('request')->name('request.')->group(function () {
        Route::get('/{id}', 'showRequest')->name('show');
        Route::get('/auth/{id}', 'showRequestForAuthenticatedUser')->name('authenticated-user-request.show');
    });
});

Route::prefix('demandes')->name('demandes.')->middleware(['not.admin', 'permanent.account', 'pension.context'])->group(function () {
    Route::prefix('virements')->name('virements.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createDemandeVirement')->name('create');
        Route::post('/', 'storeDemandeVirement')->name('store');
    });

    Route::prefix('attestations')->name('attestations.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createDemandeAttestation')->name('create');
        Route::post('/', 'storeDemandeAttestation')->name('store');
    });

    Route::prefix('transfert-cheque')->name('transfert-cheque.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createDemandeTransfertCheque')->name('create');
        Route::post('/', 'storeDemandeTransfertCheque')->name('store');
    });

    Route::prefix('arret-paiement')->name('arret-paiement.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createDemandeArretPaiement')->name('create');
        Route::post('/', 'storeDemandeArretPaiement')->name('store');
    });

    Route::prefix('demande-reinsertion')->name('demande-reinsertion.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createDemandeReinsertion')->name('create');
        Route::post('/', 'storeDemandeReinsertion')->name('store');
    });

    Route::prefix('demande-arret-virement')->name('demande-arret-virement.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createDemandeArretVirement')->name('create');
        Route::post('/', 'storeDemandeArretVirement')->name('store');
    });

    Route::prefix('preuve-existence')->name('preuve-existence.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createPreuveExistence')->name('create');
        Route::post('/', 'storePreuveExistence')->name('store');
    });

    Route::prefix('mise-a-jour')->name('mise-a-jour.')->controller(DemandeMiseAJourController::class)->group(function () {
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
    });

    Route::prefix('pension-pensionne')->name('pension-pensionne.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createDemandePensionPensionne')->name('create');
        Route::post('/', 'storeDemandePensionPensionne')->name('store');
    });

    Route::prefix('demande-etat-carriere')->name('demande-etat-carriere.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createDemandeEtatCarriere')->name('create');
        Route::post('/', 'storeDemandeEtatCarriere')->name('store');
    });

    Route::prefix('demande-adhesion')->name('demande-adhesion.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createDemandeAdhesion')->name('create');
        Route::post('/', 'storeDemandeAdhesion')->name('store');
    });

    Route::get('/demande-pension', [DemandeController::class, 'showDemandesPensionPage'])->name('demande-pension.index');

    Route::prefix('demande-pension-standard')->name('demande-pension-standard.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createDemandePensionStandard')->name('create');
        Route::post('/', 'storeDemandePensionStandard')->name('store');
    });

    Route::prefix('demande-pension-reversion')->name('demande-pension-reversion.')->controller(DemandeController::class)->group(function () {
        Route::get('/create/{demandeId?}', 'createDemandePensionReversion')->name('create');
        Route::post('/', 'storeDemandePensionReversion')->name('store');
    });

    Route::delete('/{demande}', [DemandeController::class, 'destroyDemande'])
        ->middleware('password.confirm')
        ->name('destroy');
});

Route::prefix('demandes')->middleware('not.admin')->group(function () {
    Route::post('/{demande}/documents', [DemandeDocumentController::class, 'store'])->name('demandedocument.store');
    Route::delete('/documents/{media}', [DemandeDocumentController::class, 'destroy'])->name('demandedocument.destroy');
    Route::get('/{demande}/pdf', [DemandePdfController::class, 'download'])->name('demande.pdf');
    Route::get('/{demande}/print', [DemandePdfController::class, 'print'])->name('demande.print');

    Route::middleware('password.confirm')->group(function () {
        Route::post('/{demande}/annotation', [DemandeManagementController::class, 'annotate'])->name('demande.annotate');
        Route::post('/{demande}/complement', [DemandeManagementController::class, 'requestComplement'])->name('demande.complement');
        Route::post('/{demande}/repondre-complement', [PersonalController::class, 'repondreComplement'])->name('demande.repondre-complement');
        Route::post('/transfert', [DemandeManagementController::class, 'transfererDemande'])->name('demande.transfert');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['corbeille.access', 'password.confirm', 'admin.include_soft_deleted'])->controller(DemandeManagementController::class)->group(function () {
    Route::post('/interactions/{interaction}/accepter', 'accepterReception')->name('interactions.accepter');
    Route::post('/interactions/{interaction}/refuser', 'refuserReception')->name('interactions.refuser');
    Route::post('/demandes/{demande}/affecter', 'affecterServices')->name('demandes.affecter');
    Route::post('/interactions/{interaction}/repondre', 'repondreAffectation')->name('interactions.repondre');
    Route::post('/demandes/{demande}/assigner-agent', 'assignerAgent')->name('demandes.assigner-agent');
});

Route::prefix('formalites/comptes-demandes')->name('formalites.comptes-demandes.')->middleware('role:'.User::ROLE_AGENT_FORMALITES)->controller(DemandeCreationCompteManagementController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{demandeCreationCompte}', 'show')->name('show');
    Route::get('/{demandeCreationCompte}/pieces/{media}', 'piece')->name('piece');
    Route::post('/{demandeCreationCompte}/accepter', 'accepter')->name('accepter');
    Route::post('/{demandeCreationCompte}/refuser', 'refuser')->name('refuser');
});

Route::prefix('formalites')->name('formalites.')->group(function () {
    Route::get('/representations', [BeneficiaireRepresentationHubController::class, 'index'])
        ->name('representations.index')
        ->middleware(['role_or_permission:pension_mandats.view|beneficiaire_relations.view']);

    Route::get('/suivi', [FormaliteSuiviController::class, 'index'])
        ->name('suivi.index')
        ->middleware('role:'.implode('|', FormaliteSuiviController::accessRoles()));

    Route::prefix('pension-mandats')->name('pension-mandats.')->middleware(['role_or_permission:pension_mandats.view'])->group(function () {
        Route::get('/{mandat}/pieces/{media}', [BeneficiaireRepresentationPieceController::class, 'mandatPiece'])->name('piece');
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

    Route::prefix('beneficiaire-relations')->name('beneficiaire-relations.')->middleware(['role_or_permission:admin|beneficiaire_relations.view'])->group(function () {
        Route::get('/demandes/{demande}/pieces/{media}', [BeneficiaireRepresentationPieceController::class, 'relationPiece'])->name('piece');
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
});

Route::prefix('pilotage-rendez-vous')->name('rencontres.pilotage.')->middleware('corbeille.access')->group(function () {
    Route::prefix('disponibilites')->name('disponibilites.')->controller(RdvAgentAvailabilityController::class)->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::post('/', 'update')->name('update');
    });

    Route::controller(DemandeRencontreController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{demande}', 'show')->name('show');
        Route::post('/{demande}/examiner', 'examiner')->name('examiner');
        Route::post('/{demande}/attribuer', 'attribuer')->name('attribuer');
        Route::post('/{demande}/proposer-creneau', 'proposerCreneau')->name('proposer-creneau');
        Route::post('/{demande}/accepter', 'accepter')->name('accepter');
        Route::post('/{demande}/refuser', 'refuser')->name('refuser');
        Route::post('/{demande}/clore', 'clore')->name('clore');
        Route::post('/{demande}/suite', 'enregistrerSuite')->name('suite');
        Route::post('/{demande}/confirmer-identite', 'confirmerIdentite')->name('confirmer-identite');
        Route::post('/{demande}/reattribuer-agent', 'reattribuerAgent')->name('reattribuer');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin|directeur|assistant_directeur', 'password.confirm', 'admin.include_soft_deleted'])->controller(DemandeManagementController::class)->group(function () {
    Route::post('/demandes/{demande}/approuver', 'approuver')->name('demandes.approuver');
    Route::post('/demandes/{demande}/cloturer', 'cloturer')->name('demandes.cloturer');
    Route::post('/demandes/{demande}/rejeter', 'rejeter')->name('demandes.rejeter');
    Route::post('/demandes/{demande}/rouvrir', 'rouvrir')->name('demandes.rouvrir');
});

Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/{id}/mark-read', 'markAsRead')->name('markAsRead');
    Route::get('/{id}/open', 'open')->name('open');
    Route::post('/mark-all-read', 'markAllAsRead')->name('markAllAsRead');
    Route::delete('/{id}', 'destroy')->name('destroy');
});
