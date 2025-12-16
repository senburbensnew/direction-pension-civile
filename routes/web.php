<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\FonctionnaireController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\PensionnaireController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuiSommesNousController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarouselController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\EnregistrementPensionnaireController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\MediaController;
use App\Models\Actualite;
use App\Http\Controllers\ReportController;
use App\Models\Report;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/toggle-construction', function () {
    // Flip session value (true → false, false/null → true)
    session()->put('site_under_construction', !session()->get('site_under_construction', true));

    return redirect()->back();
})->name('toggle.construction');


// Actualites routes
Route::get('actualites', [ActualiteController::class, 'index'])->name('actualites.index');
Route::get('actualites/{actualite}', [ActualiteController::class, 'show'])->name('actualites.show');
Route::get('actualites/{actualite}/download', [ActualiteController::class, 'download'])->name('actualites.download');
Route::middleware(['auth'])->group(function() {
    Route::get('admin/actualites', [ActualiteController::class, 'adminIndex'])->name('actualites.admin.index');
    Route::get('admin/actualites/create', [ActualiteController::class, 'create'])->name('actualites.create');
    Route::post('admin/actualites', [ActualiteController::class, 'store'])->name('actualites.store');
    Route::get('admin/actualites/{actualite}/edit', [ActualiteController::class, 'edit'])->name('actualites.edit');
    Route::put('admin/actualites/{actualite}', [ActualiteController::class, 'update'])->name('actualites.update');
    Route::delete('admin/actualites/{actualite}', [ActualiteController::class, 'destroy'])->name('actualites.destroy');
    Route::post('admin/actualites/{actualite}/toggle', [ActualiteController::class, 'togglePublish'])->name('actualites.toggle');
});


Route::get('/reports/view/{report}', function (Report $report) {
    return response()->file(storage_path('app/public/' . $report->file_path));
})->name('reports.view');

Route::get('rapports', [ReportController::class,'index'])->name('reports.index');
Route::get('rapports/{report}', [ReportController::class,'show'])->name('reports.show');
Route::get('rapports/{report}/download', [ReportController::class,'download'])->name('reports.download');

Route::middleware(['auth'])->group(function(){
    Route::get('admin/rapports', [ReportController::class,'adminIndex'])->name('reports.admin.index');
    Route::get('admin/rapports/create', [ReportController::class,'create'])->name('reports.create');
    Route::post('admin/rapports', [ReportController::class,'store'])->name('reports.store');
    Route::get('admin/rapports/{report}/edit', [ReportController::class,'edit'])->name('reports.edit');
    Route::put('admin/rapports/{report}', [ReportController::class,'update'])->name('reports.update');
    Route::delete('admin/rapports/{report}', [ReportController::class,'destroy'])->name('reports.destroy');
    Route::post('admin/rapports/{report}/toggle', [ReportController::class,'togglePublish'])->name('reports.toggle');
});

Route::middleware(['auth'])->group(function(){
    // Usagers (pensionnaires / fonctionnaires)
    Route::get('/mes-demandes', [DemandeController::class,'index'])->name('demandes.index');
    Route::get('/mes-demandes/{demande}', [DemandeController::class,'show'])->name('demandes.show');


    // Admin / agents
    Route::prefix('admin')->name('admin.')->middleware('can:access-admin')->group(function(){
    Route::get('/demandes', [DemandeManagementController::class,'index'])->name('demandes.index');
    Route::get('/demandes/{demande}/edit', [DemandeManagementController::class,'edit'])->name('demandes.edit');
    Route::post('/demandes/{demande}/update-status', [DemandeManagementController::class,'updateStatus'])->name('demandes.updateStatus');
    });
});

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/demandes/{demande}/progress', [DemandeApiController::class,'progress']);
});

Route::get('/', function () {
    $latestActualites = Actualite::latest()->take(6)->get(); // récupérer les 3 dernières actualités

    $recents = Report::where('status', 'published')
        ->orderBy('published_at', 'desc')
        ->limit(3)
        ->get();

    return view('home', compact('latestActualites', 'recents'));

})->name('home');

// Page Profil de la Directrice
/* Route::get('/profil-directrice', function () {
    return view('quisommesnous.profil');
})->name('profil.directrice'); */


Route::get('/mediatheque', [MediaController::class, 'index'])->name('mediatheque');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->name('newsletter.subscribe');


Route::get('/glossaire', function () {
    return view('glossaire.index');
})->name('glossaire');


Route::get('/simulateur-calcul', function () {
    return view('simulateur-calcul');
})->name('simulateur-calcul');

Route::get('/textes_documents_legaux', function () {
    return view('communication.textes_publication');
})->name('textes_documents_legaux');

Route::get('/faq', function () {
    return view('faq.index');
})->name('faq.index');


Route::get('/documents/{filename}', function ($filename) {
    $path = storage_path("app/public/documents/$filename");

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->name('documents.view');

Route::get('/documents/download/{filename}', function ($filename) {
    $path = storage_path("app/public/documents/$filename");

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path);
})->name('documents.download');

Route::get('/enregistrement-pensionnaire/create', [EnregistrementPensionnaireController::class, 'create'])->name('enregistrement-pensionnaire.create');
Route::post('/enregistrement-pensionnaire', [EnregistrementPensionnaireController::class, 'store'])->name('enregistrement-pensionnaire.store');

Route::get('/liens-utiles', function () {
    $links_fr = [
        [
            'name' => "Ministère de l'Economie et des Finances",
            'abbr' => "MEF",
            'link' => "https://mef.gouv.ht/"
        ],
        [
            'name' => "Direction Générale du Budget",
            'abbr' => "DGB",
            'link' => "https://budget.gouv.ht/"
        ],
        [
            'name' => "Administration Générale des Douanes",
            'abbr' => "AGD",
            'link' => "https://www.douane.gouv.ht/"
        ],
        [
            'name' => "Banque de la République d’Haïti",
            'abbr' => "BRH",
            'link' => "https://www.brh.ht/"
        ],
        [
            'name' => "Bureau de Monétisation des Programmes d'Aide au Développement",
            'abbr' => "BMPAD",
            'link' => "https://bmpad.gouv.ht/"
        ],
        [
            'name' => "Direction Générale des Impôts",
            'abbr' => "DGI",
            'link' => "https://dgi.gouv.ht/"
        ],
        [
            'name' => "Inspection Générale des Finances",
            'abbr' => "IGF",
            'link' => "https://igf.gouv.ht/"
        ],
        [
            'name' => "Institut Haïtien de Statistique et d'Informatique",
            'abbr' => "IHSI",
            'link' => "https://ihsi.gouv.ht/"
        ],
        [
            'name' => "Office d'Assurance Véhicules Contre Tiers",
            'abbr' => "OAVCT",
            'link' => "https://oavct.gouv.ht/"
        ],
        [
            'name' => "Société Nationale des Parcs Industriels",
            'abbr' => "SONAPI",
            'link' => "https://sonapi.gouv.ht/"
        ],
        [
            'name' => "Banque Nationale de Crédit",
            'abbr' => "BNC",
            'link' => "https://www.bnconline.com/"
        ],
    ];

    $links_en = [
        [
            'name' => "Ministry of Economy and Finance",
            'abbr' => "MEF",
            'link' => "https://mef.gouv.ht/"
        ],
        [
            'name' => "General Directorate of Budget",
            'abbr' => "DGB",
            'link' => "https://budget.gouv.ht/"
        ],
        [
            'name' => "General Administration of Customs",
            'abbr' => "AGD",
            'link' => "https://www.douane.gouv.ht/"
        ],
        [
            'name' => "Bank of the Republic of Haiti",
            'abbr' => "BRH",
            'link' => "https://www.brh.ht/"
        ],
        [
            'name' => "Office of Monetization of Development Aid Programs",
            'abbr' => "BMPAD",
            'link' => "https://bmpad.gouv.ht/"
        ],
        [
            'name' => "General Directorate of Taxes",
            'abbr' => "DGI",
            'link' => "https://dgi.gouv.ht/"
        ],
        [
            'name' => "General Inspectorate of Finance",
            'abbr' => "IGF",
            'link' => "https://igf.gouv.ht/"
        ],
        [
            'name' => "Haitian Institute of Statistics and Informatics",
            'abbr' => "IHSI",
            'link' => "https://ihsi.gouv.ht/"
        ],
        [
            'name' => "Office of Vehicle Insurance Against Third Parties",
            'abbr' => "OAVCT",
            'link' => "https://oavct.gouv.ht/"
        ],
        [
            'name' => "National Society of Industrial Parks",
            'abbr' => "SONAPI",
            'link' => "https://sonapi.gouv.ht/"
        ],
        [
            'name' => "National Credit Bank",
            'abbr' => "BNC",
            'link' => "https://www.bnconline.com/"
        ],
    ];

    // Check the current language
    if (App::getLocale() == 'fr') {
        $links = $links_fr;
    } else {
        $links = $links_en;
    }

    return view('liens-utiles.index', compact('links'));
})->name('liens-utiles');

Route::get('/contact', function () {
    return view('contact.index');
})->name('contact');


Route::get('/locale/{locale}', [App\Http\Controllers\LocaleController::class, 'switch'])
    ->name('locale');

Route::prefix('personal')->middleware('auth')->group(function () {
    Route::get('/', [PersonalController::class, 'index'])->name('personal.index');
    Route::get('/dashboard', [PersonalController::class, 'dashboard'])->name('personal.dashboard');
    Route::get('/requestsDashboard', [PersonalController::class, 'requestsDashboard'])->name('personal.requests-dashboard');
    Route::prefix('requests')->group(function () {
        Route::get('/{id}', [PersonalController::class, 'showRequest'])->name('personal.request.show');
        Route::put('/{id}', [PersonalController::class, 'updateRequest'])->name('personal.request.update');
        Route::delete('/{id}/cancel', action: [PersonalController::class, 'cancelRequest'])->name('personal.request.cancel');
    });
});

Route::get('/politique-confidentialite', function () {
    return view('privacy'); // Assurez-vous que resources/views/privacy.blade.php existe
})->name('privacy.policy');

Route::prefix('admin')->name('admin.')->group(function () {
    // Carousels
    Route::middleware(['auth'])->group(function () {
        Route::resource('carousels', CarouselController::class);
    });

    Route::controller(DocumentController::class)->group(function () {
        Route::get('/documents/upload', 'index')->name('documents.index');
        Route::post('/documents/upload', 'upload')->name('documents.upload');
    });

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('posts', PostController::class);
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
});

// Pensionnaire Routes
Route::prefix('pensionnaire')
    ->name('pensionnaire.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/demande-virement', [PensionnaireController::class, 'demandeVirement'])->name('virement-request-form');
        Route::get('/demande-attestation', [PensionnaireController::class, 'demandeAttestation'])->name('attestation-request-form');
        Route::get('/demande-transfert-cheque', [PensionnaireController::class, 'demandeTransfertCheque'])->name('check-transfer-request-form');
        Route::get('/demande-arret-paiement', [PensionnaireController::class, 'demandeArretPaiement'])->name('payment-stop-request-form');
        Route::get('/demande-reinsertion', [PensionnaireController::class, 'demandeReinsertion'])->name('reinstatement-request-form');
        Route::get('/demande-arret-virement', [PensionnaireController::class, 'demandeArretVirement'])->name('transfer-stop-request-form');
        Route::get('/preuve-existence', [PensionnaireController::class, 'preuveExistence'])->name('preuve-existence');
        Route::get('/demande-pension', [PensionnaireController::class, 'demandePension'])
        ->name('pension-request-form');
        Route::get('/demande-pension-reversion', [PensionnaireController::class, 'showPensionReversionForm'])
        ->name('pension-reversion-form');

        Route::post('/demande-virement', [PensionnaireController::class, 'processVirementRequest'])->name('process-virement-request');
        Route::post('/demande-attestation', [PensionnaireController::class, 'processAttestationRequest'])->name('process-attestation-request');
        Route::post('/demande-transfert-cheque', [PensionnaireController::class, 'processCheckTransferRequest'])->name('process-check-transfer-request');
        Route::post('/demande-arret-paiement', [PensionnaireController::class, 'processPaymentStopRequest'])->name('process-payment-stop-request');
        Route::post('/demande-reinsertion', [PensionnaireController::class, 'processReinstatementRequest'])->name('process-reinstatement-request');
        Route::post('/demande-arret-virement', [PensionnaireController::class, 'processTransferStopRequest'])->name('process-transfer-stop-request');
        Route::post('/preuve-existence', [PensionnaireController::class, 'processExistenceProofRequest'])->name('process-existence-proof-request');
    });

    Route::prefix('fonctionnaire')->name('fonctionnaire.')->middleware('auth')->group(function () {
        // Get routes
        Route::get('/demande-etat-de-carriere', [FonctionnaireController::class, 'demandeEtatCarriere'])
            ->name('career-state-form');

        Route::get('/simulation-retraite', [FonctionnaireController::class, 'retirementSimulation'])
            ->name('retirement-simulation-form');

        Route::get('/demande-pension', [FonctionnaireController::class, 'demandePension'])
            ->name('pension-request-form');

        Route::get('/demande-pension-standard', [FonctionnaireController::class, 'showPensionStandardForm'])
            ->name('pension-standard-form');

        // Post routes
        Route::post('/demande-etat-de-carriere', [FonctionnaireController::class, 'processCareerStateRequest'])
            ->name('process-career-state-request');

        Route::post('/simulation-retraite', [FonctionnaireController::class, 'processRetirementSimulation'])
            ->name('process-retirement-simulation');

        Route::post('/demande-pension', [FonctionnaireController::class, 'processPensionRequest'])
            ->name('process-pension-request');

        Route::post('/demande-pension-standard', [FonctionnaireController::class, 'processPensionStandard'])
            ->name('process-pension-standard');

        Route::post('/demande-pension-reversion', [FonctionnaireController::class, 'processPensionReversion'])
            ->name('process-pension-reversion');
    });

    Route::prefix('institution')->name('institution.')->middleware('auth')->group(function () {
        // Get routes
        Route::get('/demande-adhesion', [InstitutionController::class, 'demandeAdhesion'])
            ->name('demande-adhesion-form');

        // Post routes
        Route::post('/demande-adhesion', [InstitutionController::class, 'processDemandeAdhesion'])
            ->name('process-demande-adhesion');
    });


// Qui sommes nous Routes
Route::prefix('quisommesnous')->name('quisommesnous.')->group(function () {
    // Get routes
    Route::get('/mots', [QuiSommesNousController::class, 'mots'])->name('mots');
    Route::get('/profil', [QuiSommesNousController::class, 'profil'])->name('profil');
    Route::get('/missions', [QuiSommesNousController::class, 'missions'])->name('missions');
    Route::get('/historique', [QuiSommesNousController::class, 'historique'])->name('historique');
    Route::get('/structure-organique', [QuiSommesNousController::class, 'structureOrganique'])->name('structure-organique');
    Route::get('/financement', [QuiSommesNousController::class, 'financement'])->name('financement');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
