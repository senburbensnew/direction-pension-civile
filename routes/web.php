<?php

use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DemandeCreationCompteController;
use App\Http\Controllers\DemandeMediaController;
use App\Http\Controllers\DemandeRencontreController;
use App\Http\Controllers\DirectionDepartementaleController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GlossaireController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MediathequeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NewsletterTrackingController;
use App\Http\Controllers\NewsletterWebhookController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\QuiSommesNousController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaticPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WEB ROUTES (point d'entrée)
|--------------------------------------------------------------------------
| Fichiers inclus : auth.php, authenticated.php (auth + compte permanent),
| admin.php (role admin).
| API : routes/api.php · Console : routes/console.php · Broadcast : channels.php
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::controller(StaticPageController::class)->group(function () {
    Route::get('/simulateur-calcul', 'simulateurCalcul')->name('simulateur-calcul');
    Route::get('/politique-confidentialite', 'privacy')->name('privacy.policy');
    Route::get('/conditions-utilisation', 'terms')->name('terms.policy');
});

Route::controller(ContactController::class)->group(function () {
    Route::get('/contact', 'index')->name('contact');
    Route::post('/contact', 'store')->middleware('throttle:5,1')->name('contact.store');
});

Route::prefix('demande-compte')->name('demandes.compte.')->controller(DemandeCreationCompteController::class)->group(function () {
    Route::get('/', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::post('/ocr', 'extractOcr')->middleware('throttle:20,1')->name('ocr');
    Route::get('/disponibilite', 'availability')->middleware('throttle:60,1')->name('disponibilite');
});

Route::get('/glossaire', [GlossaireController::class, 'publicIndex'])->name('glossaire');
Route::get('/faq', [FaqController::class, 'publicIndex'])->name('faq.index');

Route::controller(PublicationController::class)->group(function () {
    Route::get('/textes_documents_legaux', 'publicIndex')->name('textes_documents_legaux');
    Route::get('/publications/{publication}/download', 'download')->name('publications.download');
});

Route::get('/services/{service:code}', [ServiceController::class, 'publicShow'])->name('services.show');
Route::get('/directions-departementales/{direction:abbr}', [DirectionDepartementaleController::class, 'publicShow'])->name('directions.show');

Route::prefix('quisommesnous')->name('quisommesnous.')->controller(QuiSommesNousController::class)->group(function () {
    Route::redirect('/presentation', '/quisommesnous/missions')->name('presentation');
    Route::redirect('/historique', '/quisommesnous/missions')->name('historique');
    Route::get('/mots', 'mots')->name('mots');
    Route::get('/profil', 'profil')->name('profil');
    Route::get('/missions', 'missions')->name('missions');
    Route::get('/structure-organique', 'structureOrganique')->name('structure-organique');
    Route::get('/financement', 'financement')->name('financement');
});

Route::get('/mediatheque', [MediathequeController::class, 'publicIndex'])->name('mediatheque');

Route::prefix('rapports')->name('reports.')->controller(ReportController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{report}', 'show')->name('show');
    Route::get('/{report}/download', 'download')->name('download');
});

Route::get('/reports/view/{report}', [ReportController::class, 'view'])->name('reports.view');

Route::prefix('actualites')->name('actualites.')->controller(ActualiteController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{actualite}', 'show')->name('show');
});

/*
|--------------------------------------------------------------------------
| Rendez-vous (RencontreMotifService, RencontreAvailabilityService, …)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'account.permanent', 'pension.portal'])->controller(DemandeRencontreController::class)->group(function () {
    Route::get('/demande-rencontre', 'create')->name('demandes.rencontre.create');
    Route::get('/demande-rencontre/calendar/reserved-slots', 'bookingReservedSlots')->name('demandes.rencontre.booking-reserved-slots');
    Route::post('/demande-rencontre', 'store')->name('demandes.rencontre.store');
    Route::get('/rendez-vous-physique', 'createPhysique')->name('demandes.rencontre.physique');
});

Route::middleware(['auth', 'account.permanent'])->group(function () {
    Route::prefix('demande-rencontre/{demande}')->name('demandes.rencontre.')->controller(DemandeRencontreController::class)->group(function () {
        Route::get('/calendar/reserved-slots', 'dossierReservedSlots')->name('dossier-reserved-slots');
        Route::post('/annuler', 'annuler')->name('annuler');
        Route::post('/documents', 'uploadDocuments')->name('documents');
        Route::post('/refuser', 'refuser')->name('refuser');
        Route::post('/accepter', 'accepter')->name('accepter');
        Route::post('/clore', 'clore')->name('clore');
        Route::post('/proposer-creneau', 'proposerCreneau')->name('proposerCreneau');
        Route::post('/reattribuer-agent', 'reattribuerAgent')->name('reattribuer');
        Route::get('/formalite', 'formalite')->name('formalite');
        Route::post('/formalite', 'enregistrerFormalite')->name('formalite.store');
    });

    Route::prefix('rendez-vous/visio/{token}')
        ->where(['token' => '[A-Fa-f0-9]{64}'])
        ->name('demandes.rencontre.')
        ->controller(DemandeRencontreController::class)
        ->group(function () {
            Route::get('/', 'visio')->name('visio');
            Route::get('/status', 'visioStatus')->name('visio.status');
        });
});

Route::prefix('newsletter')->name('newsletter.')->group(function () {
    Route::post('/souscription', [NewsletterController::class, 'souscription'])
        ->middleware('throttle:newsletter-subscribe')
        ->name('souscription');
    Route::get('/confirm/{token}', [NewsletterController::class, 'confirm'])->name('confirm');
    Route::get('/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('unsubscribe');
    Route::get('/resubscribe', [NewsletterController::class, 'showResubscribe'])->name('resubscribe');
    Route::post('/resubscribe', [NewsletterController::class, 'resubscribe'])
        ->middleware('throttle:newsletter-subscribe')
        ->name('resubscribe.store');

    Route::controller(NewsletterTrackingController::class)->group(function () {
        Route::get('/o/{token}.gif', 'open')->name('track.open');
        Route::get('/r/{token}', 'click')->name('track.click');
    });
});

Route::post('/webhooks/newsletter', [NewsletterWebhookController::class, 'handle'])->name('newsletter.webhook');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale');

Route::prefix('documents')->name('documents.')->controller(DocumentController::class)->group(function () {
    Route::get('/download/{filename}', 'download')->name('download');
    Route::get('/{filename}', 'show')->name('view');
});

Route::middleware(['auth'])->prefix('demandes/{demande}')->name('demande.')->controller(DemandeMediaController::class)->group(function () {
    Route::get('/media/{media}', 'media')->name('media.show');
    Route::get('/fichier-integre', 'embedded')->name('embedded.show');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'account.permanent'])->group(function () {
    require __DIR__.'/authenticated.php';
});

require __DIR__.'/admin.php';
