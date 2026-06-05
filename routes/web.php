<?php

use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CarouselController;
use App\Http\Controllers\InstitutionImageController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\PartenaireController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DemandeDocumentController;
use App\Http\Controllers\DemandeManagementController;
use App\Http\Controllers\DemandePdfController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GlossaireController;
use App\Http\Controllers\LienUtileController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MediathequeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuiSommesNousController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\FluxTransitionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactParameterController;
use App\Http\Controllers\DirectionDepartementaleController;
use App\Http\Controllers\DemandeRencontreController;
use App\Http\Controllers\UserController;
use App\Models\Actualite;
use App\Models\Report;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC ROUTES
// ============================================================

Route::get('/', function () {
    $latestActualites = Actualite::where('published', true)
        ->orderBy('created_at', 'desc')
        ->take(6)
        ->get();

    $recentReports = Report::where('status', 'published')
        ->orderBy('published_at', 'desc')
        ->take(3)
        ->get();

    $carousels = \App\Models\Carousel::where('status', true)->ordered()->get();

    return view('home', compact('latestActualites', 'recentReports', 'carousels'));
})->name('home');

// Static pages
Route::get('/simulateur-calcul',         fn () => view('fonctionnaire.simulateur-calcul'))->name('simulateur-calcul');
Route::get('/politique-confidentialite', fn () => view('privacy'))->name('privacy.policy');

// Content pages (DB-backed)
Route::get('/glossaire',               [GlossaireController::class,   'publicIndex'])->name('glossaire');
Route::get('/faq',                     [FaqController::class,          'publicIndex'])->name('faq.index');
Route::get('/textes_documents_legaux',              [PublicationController::class, 'publicIndex'])->name('textes_documents_legaux');
Route::get('/publications/{publication}/download',  [PublicationController::class, 'download'])->name('publications.download');
Route::get('/liens-utiles',            [LienUtileController::class,    'publicIndex'])->name('liens-utiles');

Route::get('/contact',  [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Qui sommes-nous
Route::prefix('quisommesnous')->name('quisommesnous.')->group(function () {
    Route::get('/mots',                [QuiSommesNousController::class, 'mots'])->name('mots');
    Route::get('/profil',              [QuiSommesNousController::class, 'profil'])->name('profil');
    Route::get('/missions',            [QuiSommesNousController::class, 'missions'])->name('missions');
    Route::get('/historique',          [QuiSommesNousController::class, 'historique'])->name('historique');
    Route::get('/structure-organique', [QuiSommesNousController::class, 'structureOrganique'])->name('structure-organique');
    Route::get('/financement',         [QuiSommesNousController::class, 'financement'])->name('financement');
});

// Media & content
Route::get('/mediatheque', [MediathequeController::class, 'publicIndex'])->name('mediatheque');

// Reports (public)
Route::get('rapports',                  [ReportController::class, 'index'])->name('reports.index');
Route::get('rapports/{report}',         [ReportController::class, 'show'])->name('reports.show');
Route::get('rapports/{report}/download',[ReportController::class, 'download'])->name('reports.download');
Route::get('/reports/view/{report}',    fn (Report $report) => response()->file(storage_path('app/public/' . $report->file_path)))->name('reports.view');

// Actualités (public)
Route::get('actualites',                   [ActualiteController::class, 'index'])->name('actualites.index');
Route::get('actualites/{actualite}',       [ActualiteController::class, 'show'])->name('actualites.show');
Route::get('actualites/{actualite}/download',[ActualiteController::class, 'download'])->name('actualites.download');

// Utilities
// Demande de visioconférence (public — no auth required)
Route::get('/demande-rencontre',  [DemandeRencontreController::class, 'create'])->name('demandes.rencontre.create');
Route::post('/demande-rencontre', [DemandeRencontreController::class, 'store'])->name('demandes.rencontre.store');

Route::post('/newsletter/souscription',          [NewsletterController::class, 'souscription'])->name('newsletter.souscription');
Route::get('/newsletter/unsubscribe/{token}',    [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
Route::get('/locale/{locale}',          [LocaleController::class, 'switch'])->name('locale');

// Document serving
Route::get('/documents/{filename}', function ($filename) {
    $path = storage_path("app/public/documents/$filename");
    abort_unless(file_exists($path), 404);
    return response()->file($path);
})->name('documents.view');

Route::get('/documents/download/{filename}', function ($filename) {
    $path = storage_path("app/public/documents/$filename");
    abort_unless(file_exists($path), 404);
    return response()->download($path);
})->name('documents.download');

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================

Route::middleware('auth')->group(function () {

    // ----------------------------------------------------------
    // Profile
    // ----------------------------------------------------------
    Route::get('/profile',               [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',             [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/profile-photo',[ProfileController::class, 'updateProfilePhoto'])->name('profile.profile-photo.update');
    Route::delete('/profile',            [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ----------------------------------------------------------
    // Personal dashboard & demande tracking
    // ----------------------------------------------------------
    Route::prefix('personal')->name('personal.')->middleware('not.admin')->group(function () {
        Route::get('/',                    [PersonalController::class, 'index'])->name('index');
        Route::get('/dashboard',           [PersonalController::class, 'dashboard'])->name('dashboard');
        Route::get('/requestsDashboard',   [PersonalController::class, 'requestsDashboard'])->name('requests-dashboard');
        Route::get('/dashboard-corbeille', [PersonalController::class, 'requestsDashboardCorbeille'])->name('requests-dashboard-corbeille');

        Route::middleware('corbeille.access')->group(function () {
            Route::get('/corbeille',        [PersonalController::class, 'corbeille'])->name('cart');
            Route::get('/corbeille/folder', [PersonalController::class, 'corbeilleByFolder'])->name('cart.folder');
        });

        Route::prefix('request')->group(function () {
            Route::get('/{id}',      [PersonalController::class, 'showRequest'])->name('request.show');
            Route::get('/auth/{id}', [PersonalController::class, 'showRequestForAuthenticatedUser'])->name('request.authenticated-user-request.show');
        });
    });

    // ----------------------------------------------------------
    // Demandes — create / store (all types)
    // ----------------------------------------------------------
    Route::prefix('demandes')->name('demandes.')->middleware('not.admin')->group(function () {

        // Pensionnaire
        Route::prefix('virements')->name('virements.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createDemandeVirement')->name('create');
            Route::post('/',                   'storeDemandeVirement')->name('store');
        });
        Route::prefix('attestations')->name('attestations.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createDemandeAttestation')->name('create');
            Route::post('/',                   'storeDemandeAttestation')->name('store');
        });
        Route::prefix('transfert-cheque')->name('transfert-cheque.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createDemandeTransfertCheque')->name('create');
            Route::post('/',                   'storeDemandeTransfertCheque')->name('store');
        });
        Route::prefix('arret-paiement')->name('arret-paiement.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createDemandeArretPaiement')->name('create');
            Route::post('/',                   'storeDemandeArretPaiement')->name('store');
        });
        Route::prefix('demande-reinsertion')->name('demande-reinsertion.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createDemandeReinsertion')->name('create');
            Route::post('/',                   'storeDemandeReinsertion')->name('store');
        });
        Route::prefix('demande-arret-virement')->name('demande-arret-virement.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createDemandeArretVirement')->name('create');
            Route::post('/',                   'storeDemandeArretVirement')->name('store');
        });
        Route::prefix('preuve-existence')->name('preuve-existence.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createPreuveExistence')->name('create');
            Route::post('/',                   'storePreuveExistence')->name('store');
        });
        Route::prefix('pension-pensionnaire')->name('pension-pensionnaire.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createDemandePensionPensionnaire')->name('create');
            Route::post('/',                   'storeDemandePensionPensionnaire')->name('store');
        });

        // Fonctionnaire
        Route::prefix('demande-etat-carriere')->name('demande-etat-carriere.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createDemandeEtatCarriere')->name('create');
            Route::post('/',                   'storeDemandeEtatCarriere')->name('store');
        });

        // Institution
        Route::prefix('demande-adhesion')->name('demande-adhesion.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createDemandeAdhesion')->name('create');
            Route::post('/',                   'storeDemandeAdhesion')->name('store');
        });

        // Pension (fonctionnaire / institution)
        Route::get('/demande-pension',     [DemandeController::class, 'showDemandesPensionPage'])->name('demande-pension.index');
        Route::prefix('demande-pension-standard')->name('demande-pension-standard.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createDemandePensionStandard')->name('create');
            Route::post('/',                   'storeDemandePensionStandard')->name('store');
        });
        Route::prefix('demande-pension-reversion')->name('demande-pension-reversion.')->controller(DemandeController::class)->group(function () {
            Route::get('/create/{demandeId?}', 'createDemandePensionReversion')->name('create');
            Route::post('/',                   'storeDemandePensionReversion')->name('store');
        });

        // Lifecycle
        Route::delete('/{demande}', [DemandeController::class, 'destroyDemande'])->name('destroy');
    });

    // Demande actions — kept in a separate group with no name prefix to preserve legacy names
    Route::prefix('demandes')->middleware('not.admin')->group(function () {
        Route::post('/{demande}/documents',           [DemandeDocumentController::class, 'store'])->name('demandedocument.store');
        Route::delete('/documents/{document}',        [DemandeDocumentController::class, 'destroy'])->name('demandedocument.destroy');
        Route::get('/{demande}/pdf',                  [DemandePdfController::class, 'download'])->name('demande.pdf');
        Route::get('/{demande}/print',                [DemandePdfController::class, 'print'])->name('demande.print');
        Route::post('/{demande}/annotation',          [DemandeManagementController::class, 'annotate'])->name('demande.annotate');
        Route::post('/{demande}/complement',          [DemandeManagementController::class, 'requestComplement'])->name('demande.complement');
        Route::post('/{demande}/repondre-complement', [PersonalController::class, 'repondreComplement'])->name('demande.repondre-complement');
        Route::post('/transfert',                     [DemandeManagementController::class, 'transfererDemande'])->name('demande.transfert');
    });

    // ----------------------------------------------------------
    // Notifications
    // ----------------------------------------------------------
    Route::prefix('notifications')->name('notifications.')->middleware('not.admin')->group(function () {
        Route::get('/',                  [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read',   [NotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::get('/{id}/open',         [NotificationController::class, 'open'])->name('open');
        Route::post('/mark-all-read',    [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
        Route::delete('/{id}',           [NotificationController::class, 'destroy'])->name('destroy');
    });
});

// ============================================================
// ADMIN ROUTES
// ============================================================

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');

    // Settings & dev tools
    Route::get('/settings',            [AdminController::class, 'settings'])->name('settings');
    Route::get('/toggle-construction', function () {
        session()->put('site_under_construction', ! session()->get('site_under_construction', true));
        return redirect()->back();
    })->name('toggle.construction');

    // Users, carousels, posts
    Route::resource('users',     UserController::class);
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('carousels/reorder', [CarouselController::class, 'reorder'])->name('carousels.reorder');
    Route::resource('carousels', CarouselController::class);
    Route::resource('posts',     PostController::class);

    // Services, roles, permissions
    Route::get('/services',    [ServiceController::class, 'index'])->name('services.index');
    Route::get('/roles',       [RoleController::class, 'index'])->name('roles.index');
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

    // Flux transitions (workflow circuit)
    Route::get('/flux-transitions',                               [FluxTransitionController::class, 'index'])->name('flux-transitions.index');
    Route::post('/flux-transitions',                              [FluxTransitionController::class, 'store'])->name('flux-transitions.store');
    Route::patch('/flux-transitions/{fluxTransition}',            [FluxTransitionController::class, 'update'])->name('flux-transitions.update');
    Route::post('/flux-transitions/{fluxTransition}/move-up',     [FluxTransitionController::class, 'moveUp'])->name('flux-transitions.move-up');
    Route::post('/flux-transitions/{fluxTransition}/move-down',   [FluxTransitionController::class, 'moveDown'])->name('flux-transitions.move-down');
    Route::delete('/flux-transitions/{fluxTransition}',                              [FluxTransitionController::class, 'destroy'])->name('flux-transitions.destroy');
    Route::post('/flux-transitions/required',                                         [FluxTransitionController::class, 'storeRequired'])->name('flux-transitions.required.store');
    Route::delete('/flux-transitions/required/{requiredCircuitService}',              [FluxTransitionController::class, 'destroyRequired'])->name('flux-transitions.required.destroy');

    // Document uploads
    Route::get('/documents/upload',  [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');

    // Demande management (agent / direction)
    Route::get('/demandes',                          [DemandeManagementController::class, 'index'])->name('demandes.index');
    Route::get('/demandes/{demande}',                [DemandeManagementController::class, 'edit'])->name('demandes.show');
    Route::post('/demandes/{demande}/update-status', [DemandeManagementController::class, 'updateStatus'])->name('demandes.updateStatus');

    // Transfer reception
    Route::post('/workflows/{workflow}/accepter', [DemandeManagementController::class, 'accepterReception'])->name('workflows.accepter');
    Route::post('/workflows/{workflow}/refuser',  [DemandeManagementController::class, 'refuserReception'])->name('workflows.refuser');

    // Décision finale Direction
    Route::post('/demandes/{demande}/approuver', [DemandeManagementController::class, 'approuver'])->name('demandes.approuver');
    Route::post('/demandes/{demande}/cloturer',  [DemandeManagementController::class, 'cloturer'])->name('demandes.cloturer');
    Route::post('/demandes/{demande}/rejeter',   [DemandeManagementController::class, 'rejeter'])->name('demandes.rejeter');
    Route::post('/demandes/{demande}/annuler',   [DemandeManagementController::class, 'annuler'])->name('demandes.annuler');

    // Affectations
    Route::post('/demandes/{demande}/affecter',         [DemandeManagementController::class, 'affecterServices'])->name('demandes.affecter');
    Route::post('/affectations/{affectation}/repondre', [DemandeManagementController::class, 'repondreAffectation'])->name('affectations.repondre');

    // Reports management (keeps original route names)
    Route::get('rapports',                   [ReportController::class, 'adminIndex'])->name('reports.admin.index');
    Route::get('rapports/create',            [ReportController::class, 'create'])->name('reports.create');
    Route::post('rapports',                  [ReportController::class, 'store'])->name('reports.store');
    Route::get('rapports/{report}/edit',     [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('rapports/{report}',          [ReportController::class, 'update'])->name('reports.update');
    Route::delete('rapports/{report}',       [ReportController::class, 'destroy'])->name('reports.destroy');
    Route::post('rapports/{report}/toggle',  [ReportController::class, 'togglePublish'])->name('reports.toggle');

    // Actualités management (keeps original route names)
    Route::get('actualites',                     [ActualiteController::class, 'adminIndex'])->name('actualites.admin.index');
    Route::get('actualites/create',              [ActualiteController::class, 'create'])->name('actualites.create');
    Route::post('actualites',                    [ActualiteController::class, 'store'])->name('actualites.store');
    Route::get('actualites/{actualite}/edit',    [ActualiteController::class, 'edit'])->name('actualites.edit');
    Route::put('actualites/{actualite}',         [ActualiteController::class, 'update'])->name('actualites.update');
    Route::delete('actualites/{actualite}',      [ActualiteController::class, 'destroy'])->name('actualites.destroy');
    Route::post('actualites/{actualite}/toggle', [ActualiteController::class, 'togglePublish'])->name('actualites.toggle');

    // Newsletter admin
    Route::get('newsletter',                                    [NewsletterController::class, 'adminIndex'])->name('newsletter.admin.index');
    Route::get('newsletter/compose',                           [NewsletterController::class, 'compose'])->name('newsletter.compose');
    Route::post('newsletter/send',                             [NewsletterController::class, 'send'])->name('newsletter.send');
    Route::get('newsletter/export',                            [NewsletterController::class, 'export'])->name('newsletter.export');
    Route::delete('newsletter/{newsletter}',                   [NewsletterController::class, 'destroy'])->name('newsletter.destroy');
    Route::delete('newsletter/campaigns/{campaign}',           [NewsletterController::class, 'destroyCampaign'])->name('newsletter.campaigns.destroy');

    // Contact admin
    Route::get('contacts',                           [ContactController::class, 'adminIndex'])->name('contacts.index');
    Route::get('contacts/{contact}',                 [ContactController::class, 'adminShow'])->name('contacts.show');
    Route::post('contacts/{contact}/read',           [ContactController::class, 'markRead'])->name('contacts.markRead');
    Route::post('contacts/{contact}/unread',         [ContactController::class, 'markUnread'])->name('contacts.markUnread');
    Route::post('contacts/mark-all-read',            [ContactController::class, 'markAllRead'])->name('contacts.markAllRead');
    Route::delete('contacts/{contact}',              [ContactController::class, 'adminDestroy'])->name('contacts.destroy');

    // FAQ admin
    Route::get('faq',                               [FaqController::class, 'adminIndex'])->name('faq.index');
    Route::post('faq',                              [FaqController::class, 'store'])->name('faq.store');
    Route::put('faq/{faqItem}',                     [FaqController::class, 'update'])->name('faq.update');
    Route::delete('faq/{faqItem}',                  [FaqController::class, 'destroy'])->name('faq.destroy');
    Route::post('faq/{faqItem}/toggle',             [FaqController::class, 'togglePublish'])->name('faq.toggle');

    // Glossaire admin
    Route::get('glossaire',                         [GlossaireController::class, 'adminIndex'])->name('glossaire.index');
    Route::post('glossaire',                        [GlossaireController::class, 'store'])->name('glossaire.store');
    Route::put('glossaire/{glossaireTerm}',         [GlossaireController::class, 'update'])->name('glossaire.update');
    Route::delete('glossaire/{glossaireTerm}',      [GlossaireController::class, 'destroy'])->name('glossaire.destroy');
    Route::post('glossaire/{glossaireTerm}/toggle', [GlossaireController::class, 'togglePublish'])->name('glossaire.toggle');

    // Liens utiles admin
    Route::get('liens-utiles',                      [LienUtileController::class, 'adminIndex'])->name('liens-utiles.index');
    Route::post('liens-utiles',                     [LienUtileController::class, 'store'])->name('liens-utiles.store');
    Route::put('liens-utiles/{lienUtile}',          [LienUtileController::class, 'update'])->name('liens-utiles.update');
    Route::delete('liens-utiles/{lienUtile}',       [LienUtileController::class, 'destroy'])->name('liens-utiles.destroy');
    Route::post('liens-utiles/{lienUtile}/toggle',  [LienUtileController::class, 'togglePublish'])->name('liens-utiles.toggle');

    // Publications admin
    Route::get('publications',                      [PublicationController::class, 'adminIndex'])->name('publications.index');
    Route::post('publications',                     [PublicationController::class, 'store'])->name('publications.store');
    Route::put('publications/{publication}',        [PublicationController::class, 'update'])->name('publications.update');
    Route::delete('publications/{publication}',     [PublicationController::class, 'destroy'])->name('publications.destroy');
    Route::post('publications/{publication}/toggle',[PublicationController::class, 'togglePublish'])->name('publications.toggle');

    // Médiathèque admin
    Route::get('mediatheque',                           [MediathequeController::class, 'adminIndex'])->name('mediatheque.index');
    Route::post('mediatheque',                          [MediathequeController::class, 'store'])->name('mediatheque.store');
    Route::put('mediatheque/{mediathequeItem}',         [MediathequeController::class, 'update'])->name('mediatheque.update');
    Route::delete('mediatheque/{mediathequeItem}',      [MediathequeController::class, 'destroy'])->name('mediatheque.destroy');
    Route::post('mediatheque/{mediathequeItem}/toggle', [MediathequeController::class, 'togglePublish'])->name('mediatheque.toggle');

    // Institution en Images admin
    Route::get('institution-images',                          [InstitutionImageController::class, 'index'])->name('institution-images.index');
    Route::get('institution-images/create',                   [InstitutionImageController::class, 'create'])->name('institution-images.create');
    Route::post('institution-images',                         [InstitutionImageController::class, 'store'])->name('institution-images.store');
    Route::get('institution-images/{institutionImage}/edit',  [InstitutionImageController::class, 'edit'])->name('institution-images.edit');
    Route::put('institution-images/{institutionImage}',       [InstitutionImageController::class, 'update'])->name('institution-images.update');
    Route::delete('institution-images/{institutionImage}',    [InstitutionImageController::class, 'destroy'])->name('institution-images.destroy');
    Route::post('institution-images/reorder',                 [InstitutionImageController::class, 'reorder'])->name('institution-images.reorder');

    // Officiels / Présentations admin
    Route::get('officiels',                          [OfficialController::class, 'index'])->name('officials.index');
    Route::get('officiels/create',                   [OfficialController::class, 'create'])->name('officials.create');
    Route::post('officiels',                         [OfficialController::class, 'store'])->name('officials.store');
    Route::get('officiels/{official}/edit',          [OfficialController::class, 'edit'])->name('officials.edit');
    Route::put('officiels/{official}',               [OfficialController::class, 'update'])->name('officials.update');
    Route::delete('officiels/{official}',            [OfficialController::class, 'destroy'])->name('officials.destroy');

    // Directions Départementales admin
    Route::get('directions',                            [DirectionDepartementaleController::class, 'index'])->name('directions.index');
    Route::post('directions',                           [DirectionDepartementaleController::class, 'store'])->name('directions.store');
    Route::put('directions/{direction}',                [DirectionDepartementaleController::class, 'update'])->name('directions.update');
    Route::delete('directions/{direction}',             [DirectionDepartementaleController::class, 'destroy'])->name('directions.destroy');

    // Contact parameters admin
    Route::get('contact-parameters',                   [ContactParameterController::class, 'index'])->name('contact-parameters.index');
    Route::put('contact-parameters',                   [ContactParameterController::class, 'update'])->name('contact-parameters.update');

    // Partenaires admin
    Route::get('partenaires',                        [PartenaireController::class, 'index'])->name('partenaires.index');
    Route::get('partenaires/create',                 [PartenaireController::class, 'create'])->name('partenaires.create');
    Route::post('partenaires',                       [PartenaireController::class, 'store'])->name('partenaires.store');
    Route::get('partenaires/{partenaire}/edit',      [PartenaireController::class, 'edit'])->name('partenaires.edit');
    Route::put('partenaires/{partenaire}',           [PartenaireController::class, 'update'])->name('partenaires.update');
    Route::delete('partenaires/{partenaire}',        [PartenaireController::class, 'destroy'])->name('partenaires.destroy');
    Route::post('partenaires/reorder',               [PartenaireController::class, 'reorder'])->name('partenaires.reorder');
});

require __DIR__ . '/auth.php';
