<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\FonctionnaireController;
use App\Http\Controllers\PensionnaireController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuiSommesNousController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarouselController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PersonalController;

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



Route::get('/', function () {
    return view('home');
})->name('home');

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
    ->middleware('auth') // Ensure user is logged in
    ->group(function () {
        // Get routes
        Route::get('/demande-virement', [PensionnaireController::class, 'demandeVirement'])->name('virement-request-form');
        Route::get('/demande-attestation', [PensionnaireController::class, 'demandeAttestation'])->name('attestation-request-form');
        Route::get('/demande-transfert-cheque', [PensionnaireController::class, 'demandeTransfertCheque'])->name('check-transfer-request-form');
        Route::get('/demande-arret-paiement', [PensionnaireController::class, 'demandeArretPaiement'])->name('payment-stop-request-form');
        Route::get('/demande-reinsertion', [PensionnaireController::class, 'demandeReinsertion'])->name('reinstatement-request-form');
        Route::get('/demande-arret-virement', [PensionnaireController::class, 'demandeArretVirement'])->name('transfer-stop-request-form');

        // Post routes
        Route::post('/demande-virement', [PensionnaireController::class, 'processVirementRequest'])->name('process-virement-request');
        Route::post('/demande-attestation', [PensionnaireController::class, 'processAttestationRequest'])->name('process-attestation-request');
        Route::post('/demande-transfert-cheque', [PensionnaireController::class, 'processCheckTransferRequest'])->name('process-check-transfer-request');
        Route::post('/demande-arret-paiement', [PensionnaireController::class, 'processPaymentStopRequest'])->name('process-payment-stop-request');
        Route::post('/demande-reinsertion', [PensionnaireController::class, 'processReinstatementRequest'])->name('process-reinstatement-request');
        Route::post('/demande-arret-virement', [PensionnaireController::class, 'processTransferStopRequest'])->name('process-transfer-stop-request');
    });

    Route::prefix('fonctionnaire')->name('fonctionnaire.')->middleware('auth')->group(function () {
        // Get routes
        Route::get('/demande-etat-de-carriere', [FonctionnaireController::class, 'demandeEtatCarriere'])->name('career-state-form');
        Route::get('/simulation-retraite', [FonctionnaireController::class, 'retirementSimulation'])->name('retirement-simulation-form');
        Route::get('/demande-pension', [FonctionnaireController::class, 'demandePension'])->name('pension-request-form');
    
        // Post routes
        Route::post('/demande-etat-de-carriere', [FonctionnaireController::class, 'processCareerStateRequest'])->name('process-career-state-request');
        Route::post('/simulation-retraite', [FonctionnaireController::class, 'processRetirementSimulation'])->name('process-retirement-simulation');
        Route::post('/demande-pension', [FonctionnaireController::class, 'processPensionRequest'])->name('process-pension-request');
    });


// Qui sommes nous Routes
Route::prefix('quisommesnous')->name('quisommesnous.')->group(function () {
    // Get routes
    Route::get('/mots', [QuiSommesNousController::class, 'mots'])->name('mots');
    Route::get('/missions', [QuiSommesNousController::class, 'missions'])->name('missions');
    Route::get('/historique', [QuiSommesNousController::class, 'historique'])->name('historique');
    Route::get('/structure-organique', [QuiSommesNousController::class, 'structureOrganique'])->name('structure-organique');
    Route::get('/financement', [QuiSommesNousController::class, 'financement'])->name('financement');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
