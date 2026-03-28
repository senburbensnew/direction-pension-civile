<?php

use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CarouselController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DemandeDocumentController;
use App\Http\Controllers\DemandeManagementController;
use App\Http\Controllers\DemandePdfController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuiSommesNousController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
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

    return view('home', compact('latestActualites', 'recentReports'));
})->name('home');

// Static pages
Route::get('/glossaire',                fn () => view('glossaire.index'))->name('glossaire');
Route::get('/simulateur-calcul',        fn () => view('fonctionnaire.simulateur-calcul'))->name('simulateur-calcul');
Route::get('/textes_documents_legaux',  fn () => view('communication.textes_publication'))->name('textes_documents_legaux');
Route::get('/faq',                      fn () => view('faq.index'))->name('faq.index');
Route::get('/contact',                  fn () => view('contact.index'))->name('contact');
Route::get('/politique-confidentialite',fn () => view('privacy'))->name('privacy.policy');

Route::get('/liens-utiles', function () {
    $links = [
        ['name' => "Ministère de l'Economie et des Finances",                     'abbr' => 'MEF',   'link' => 'https://mef.gouv.ht/'],
        ['name' => "Direction Générale du Budget",                                 'abbr' => 'DGB',   'link' => 'https://budget.gouv.ht/'],
        ['name' => "Administration Générale des Douanes",                          'abbr' => 'AGD',   'link' => 'https://www.douane.gouv.ht/'],
        ['name' => "Banque de la République d'Haïti",                              'abbr' => 'BRH',   'link' => 'https://www.brh.ht/'],
        ['name' => "Bureau de Monétisation des Programmes d'Aide au Développement",'abbr' => 'BMPAD', 'link' => 'https://bmpad.gouv.ht/'],
        ['name' => "Direction Générale des Impôts",                                'abbr' => 'DGI',   'link' => 'https://dgi.gouv.ht/'],
        ['name' => "Inspection Générale des Finances",                             'abbr' => 'IGF',   'link' => 'https://igf.gouv.ht/'],
        ['name' => "Institut Haïtien de Statistique et d'Informatique",            'abbr' => 'IHSI',  'link' => 'https://ihsi.gouv.ht/'],
        ["name" => "Office d'Assurance Véhicules Contre Tiers",                    'abbr' => 'OAVCT', 'link' => 'https://oavct.gouv.ht/'],
        ['name' => "Société Nationale des Parcs Industriels",                      'abbr' => 'SONAPI','link' => 'https://sonapi.gouv.ht/'],
        ['name' => "Banque Nationale de Crédit",                                   'abbr' => 'BNC',   'link' => 'https://www.bnconline.com/'],
    ];
    return view('liens-utiles.index', compact('links'));
})->name('liens-utiles');

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
Route::get('/mediatheque', [MediaController::class, 'index'])->name('mediatheque');

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
Route::post('/newsletter/souscription', [NewsletterController::class, 'souscription'])->name('newsletter.souscription');
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
    Route::prefix('personal')->name('personal.')->group(function () {
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
    Route::prefix('demandes')->name('demandes.')->middleware('auth')->group(function () {

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
    Route::prefix('demandes')->group(function () {
        Route::post('/{demande}/documents',           [DemandeDocumentController::class, 'store'])->name('demandedocument.store');
        Route::delete('/documents/{document}',        [DemandeDocumentController::class, 'destroy'])->name('demandedocument.destroy');
        Route::get('/{demande}/pdf',                  [DemandePdfController::class, 'download'])->name('demande.pdf');
        Route::post('/{demande}/annotation',          [DemandeManagementController::class, 'annotate'])->name('demande.annotate');
        Route::post('/{demande}/complement',          [DemandeManagementController::class, 'requestComplement'])->name('demande.complement');
        Route::post('/{demande}/repondre-complement', [PersonalController::class, 'repondreComplement'])->name('demande.repondre-complement');
        Route::post('/transfert',                     [DemandeManagementController::class, 'transfererDemande'])->name('demande.transfert');
    });

    // ----------------------------------------------------------
    // Notifications
    // ----------------------------------------------------------
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                  [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read',   [NotificationController::class, 'markAsRead'])->name('markAsRead');
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
    Route::resource('carousels', CarouselController::class);
    Route::resource('posts',     PostController::class);

    // Services, roles, permissions
    Route::get('/services',    [ServiceController::class, 'index'])->name('services.index');
    Route::get('/roles',       [RoleController::class, 'index'])->name('roles.index');
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

    // Document uploads
    Route::get('/documents/upload',  [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');

    // Demande management (agent / direction)
    Route::get('/demandes',                          [DemandeManagementController::class, 'index'])->name('demandes.index');
    Route::get('/demandes/{demande}',                [DemandeManagementController::class, 'edit'])->name('demandes.show');
    Route::post('/demandes/{demande}/update-status', [DemandeManagementController::class, 'updateStatus'])->name('demandes.updateStatus');

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
});

require __DIR__ . '/auth.php';
