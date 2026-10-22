<?php

use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\Admin\ActiveSessionController;
use App\Http\Controllers\Admin\EmailTestController;
use App\Http\Controllers\Admin\NewsletterCampaignController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;
use App\Http\Controllers\Admin\QueueMonitorController;
use App\Http\Controllers\Admin\RdvSettingsController;
use App\Http\Controllers\Admin\SmsTestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CarouselController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactParameterController;
use App\Http\Controllers\ContactSubjectController;
use App\Http\Controllers\DemandeCreationCompteController;
use App\Http\Controllers\DemandeManagementController;
use App\Http\Controllers\DemandeMediaController;
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
use App\Http\Controllers\NewsletterTrackingController;
use App\Http\Controllers\NewsletterWebhookController;
use App\Http\Controllers\OcrTestController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\PartenaireController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\QuiSommesNousController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowStepController;
use App\Models\Actualite;
use App\Models\Carousel;
use App\Models\Report;
use App\Support\Storage\UploadPaths;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

    $carousels = Carousel::where(
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
        )->name('presentation');

        Route::redirect(
            '/historique',
            '/quisommesnous/missions'
        )->name('historique');

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
            'app/public/'.$report->file_path
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
    'account.permanent',
    'pension.portal',
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
| Actions sur un rendez-vous — pensionné, agent_rdv, admin, direction
|--------------------------------------------------------------------------
|
| L'autorisation fine est faite dans le contrôleur :
|   - canCancelDemande() pour annuler
|   - authorizeRdvAgent() pour refuser / accepter / clore / proposerCreneau
|
*/

Route::middleware(['auth', 'account.permanent'])->group(function () {
    Route::post(
        '/demande-rencontre/{demande}/annuler',
        [DemandeRencontreController::class, 'annuler']
    )->name('demandes.rencontre.annuler');

    Route::post(
        '/demande-rencontre/{demande}/documents',
        [DemandeRencontreController::class, 'uploadDocuments']
    )->name('demandes.rencontre.documents');

    Route::post(
        '/demande-rencontre/{demande}/refuser',
        [DemandeRencontreController::class, 'refuser']
    )->name('demandes.rencontre.refuser');

    Route::post(
        '/demande-rencontre/{demande}/accepter',
        [DemandeRencontreController::class, 'accepter']
    )->name('demandes.rencontre.accepter');

    Route::post(
        '/demande-rencontre/{demande}/clore',
        [DemandeRencontreController::class, 'clore']
    )->name('demandes.rencontre.clore');

    Route::post(
        '/demande-rencontre/{demande}/proposer-creneau',
        [DemandeRencontreController::class, 'proposerCreneau']
    )->name('demandes.rencontre.proposerCreneau');

    Route::post(
        '/demande-rencontre/{demande}/reattribuer-agent',
        [DemandeRencontreController::class, 'reattribuerAgent']
    )->name('demandes.rencontre.reattribuer');

    Route::get(
        '/demande-rencontre/{demande}/formalite',
        [DemandeRencontreController::class, 'formalite']
    )->name('demandes.rencontre.formalite');

    Route::post(
        '/demande-rencontre/{demande}/formalite',
        [DemandeRencontreController::class, 'enregistrerFormalite']
    )->name('demandes.rencontre.formalite.store');
});

/*
|--------------------------------------------------------------------------
| Visioconférence
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'account.permanent'])->group(function () {
    Route::get(
        '/rendez-vous/visio/{token}',
        [DemandeRencontreController::class, 'visio']
    )
        ->where(
            'token',
            '[A-Fa-f0-9]{64}'
        )
        ->name('demandes.rencontre.visio');

    Route::get(
        '/rendez-vous/visio/{token}/status',
        [DemandeRencontreController::class, 'visioStatus']
    )
        ->where(
            'token',
            '[A-Fa-f0-9]{64}'
        )
        ->name('demandes.rencontre.visio.status');
});

/*
|--------------------------------------------------------------------------
| Newsletter
|--------------------------------------------------------------------------
*/

Route::post(
    '/newsletter/souscription',
    [NewsletterController::class, 'souscription']
)->middleware('throttle:newsletter-subscribe')
    ->name('newsletter.souscription');

Route::get(
    '/newsletter/confirm/{token}',
    [NewsletterController::class, 'confirm']
)->name('newsletter.confirm');

Route::get(
    '/newsletter/unsubscribe/{token}',
    [NewsletterController::class, 'unsubscribe']
)->name('newsletter.unsubscribe');

Route::get('/newsletter/resubscribe', [NewsletterController::class, 'showResubscribe'])
    ->name('newsletter.resubscribe');
Route::post('/newsletter/resubscribe', [NewsletterController::class, 'resubscribe'])
    ->middleware('throttle:newsletter-subscribe')
    ->name('newsletter.resubscribe.store');

Route::get('/newsletter/o/{token}.gif', [NewsletterTrackingController::class, 'open'])
    ->name('newsletter.track.open');
Route::get('/newsletter/r/{token}', [NewsletterTrackingController::class, 'click'])
    ->name('newsletter.track.click');

Route::post('/webhooks/newsletter', [NewsletterWebhookController::class, 'handle'])
    ->name('newsletter.webhook');

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
    function (string $filename) {
        $filename = basename($filename);
        $storagePath = UploadPaths::cms('publications').'/'.$filename;
        $disk = Storage::disk(UploadPaths::publicDisk());

        abort_unless($disk->exists($storagePath), 404);

        return response()->file($disk->path($storagePath));
    }
)->name('documents.view');

Route::get(
    '/documents/download/{filename}',
    function (string $filename) {
        $filename = basename($filename);
        $storagePath = UploadPaths::cms('publications').'/'.$filename;
        $disk = Storage::disk(UploadPaths::publicDisk());

        abort_unless($disk->exists($storagePath), 404);

        return response()->download($disk->path($storagePath));
    }
)->name('documents.download');

/*
|--------------------------------------------------------------------------
| Demande files (auth — public & private disks)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get(
        '/demandes/{demande}/media/{media}',
        [DemandeMediaController::class, 'media']
    )->name('demande.media.show');

    Route::get(
        '/demandes/{demande}/fichier-integre',
        [DemandeMediaController::class, 'embedded']
    )->name('demande.embedded.show');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
| Authenticated app routes (auth + account.permanent) live in routes/authenticated.php
| and are registered in RouteServiceProvider.
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

        Route::get(
            '/email-test',
            [EmailTestController::class, 'create']
        )->name('email.test');

        Route::post(
            '/email-test',
            [EmailTestController::class, 'store']
        )->name('email.test.store');

        Route::get(
            '/sms-test',
            [SmsTestController::class, 'create']
        )->name('sms.test');

        Route::post(
            '/sms-test',
            [SmsTestController::class, 'store']
        )->name('sms.test.store');

        Route::get('/queue', [QueueMonitorController::class, 'index'])->name('queue.index');
        Route::get('/queue/failed/{uuid}', [QueueMonitorController::class, 'showFailed'])->name('queue.failed.show');
        Route::post('/queue/failed/{uuid}/retry', [QueueMonitorController::class, 'retryFailed'])->name('queue.failed.retry');
        Route::delete('/queue/failed/{uuid}', [QueueMonitorController::class, 'forgetFailed'])->name('queue.failed.forget');
        Route::post('/queue/failed/retry-all', [QueueMonitorController::class, 'retryAllFailed'])->name('queue.failed.retry-all');
        Route::delete('/queue/pending/{id}', [QueueMonitorController::class, 'destroyPending'])->name('queue.pending.destroy');

        Route::post(
            '/toggle-maintenance',
            function () {
                $current = DB::table(
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

                DB::table(
                    'parameters'
                )
                    ->where(
                        'name',
                        'is_maintenance_mode'
                    )
                    ->update([
                        'value' => $next,
                    ]);

                Cache::forget(
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
        | Users
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'users',
            UserController::class
        )->except(['destroy']);

        Route::patch(
            'users/{user}/toggle-active',
            [
                UserController::class,
                'toggleActive',
            ]
        )->name('users.toggle-active');

        Route::post(
            'users/{user}/reset-password',
            [UserController::class, 'resetPassword']
        )->name('users.reset-password');

        Route::get(
            'sessions',
            [ActiveSessionController::class, 'index']
        )->name('sessions.index');

        Route::delete(
            'sessions/{session}',
            [ActiveSessionController::class, 'destroy']
        )->name('sessions.destroy');

        Route::delete(
            'sessions/user/{user}',
            [ActiveSessionController::class, 'destroyUser']
        )->name('sessions.destroy-user');

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

        Route::post(
            '/roles',
            [RoleController::class, 'store']
        )->name('roles.store');

        Route::put(
            '/roles/{role}',
            [RoleController::class, 'update']
        )->name('roles.update');

        Route::delete(
            '/roles/{role}',
            [RoleController::class, 'destroy']
        )->name('roles.destroy');

        Route::get(
            '/permissions',
            [PermissionController::class, 'index']
        )->name('permissions.index');

        Route::post(
            '/permissions',
            [PermissionController::class, 'store']
        )->name('permissions.store');

        Route::put(
            '/permissions/{permission}',
            [PermissionController::class, 'update']
        )->name('permissions.update');

        Route::delete(
            '/permissions/{permission}',
            [PermissionController::class, 'destroy']
        )->name('permissions.destroy');

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
        )->middleware('password.confirm')->name('demandes.updateStatus');

        Route::post(
            '/demandes/{demande}/annuler',
            [DemandeManagementController::class, 'annuler']
        )->middleware('password.confirm')->name('demandes.annuler');

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

        Route::get('newsletter/import', [NewsletterSubscriberController::class, 'importForm'])
            ->name('newsletter.import');
        Route::post('newsletter/import', [NewsletterSubscriberController::class, 'import'])
            ->name('newsletter.import.store');

        Route::get(
            'newsletter/export',
            [NewsletterController::class, 'export']
        )->name('newsletter.export');

        Route::get('newsletter/campaigns/create', [NewsletterCampaignController::class, 'create'])
            ->name('newsletter.campaigns.create');
        Route::post('newsletter/campaigns', [NewsletterCampaignController::class, 'store'])
            ->name('newsletter.campaigns.store');
        Route::get('newsletter/campaigns/{campaign}', [NewsletterCampaignController::class, 'show'])
            ->name('newsletter.campaigns.show');
        Route::get('newsletter/campaigns/{campaign}/edit', [NewsletterCampaignController::class, 'edit'])
            ->name('newsletter.campaigns.edit');
        Route::put('newsletter/campaigns/{campaign}', [NewsletterCampaignController::class, 'update'])
            ->name('newsletter.campaigns.update');
        Route::post('newsletter/campaigns/{campaign}/duplicate', [NewsletterCampaignController::class, 'duplicate'])
            ->name('newsletter.campaigns.duplicate');
        Route::post('newsletter/campaigns/{campaign}/test', [NewsletterCampaignController::class, 'test'])
            ->name('newsletter.campaigns.test');
        Route::post('newsletter/campaigns/{campaign}/cancel', [NewsletterCampaignController::class, 'cancel'])
            ->name('newsletter.campaigns.cancel');

        Route::get(
            'newsletter/compose',
            [NewsletterController::class, 'compose']
        )->name('newsletter.compose');

        Route::post(
            'newsletter/send',
            [NewsletterController::class, 'send']
        )->name('newsletter.send');

        Route::delete(
            'newsletter/campaigns/{campaign}',
            [NewsletterCampaignController::class, 'destroy']
        )->name('newsletter.campaigns.destroy');

        Route::delete(
            'newsletter/{newsletter}',
            [NewsletterController::class, 'destroy']
        )->name('newsletter.destroy');

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

        Route::get(
            'rdv-settings',
            [RdvSettingsController::class, 'index']
        )->name('rdv-settings.index');

        Route::put(
            'rdv-settings',
            [RdvSettingsController::class, 'update']
        )->name('rdv-settings.update');

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
