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
use App\Http\Controllers\DemandeManagementController;
use App\Http\Controllers\DirectionDepartementaleController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FluxTransitionController;
use App\Http\Controllers\GlossaireController;
use App\Http\Controllers\InstitutionImageController;
use App\Http\Controllers\MediathequeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OcrTestController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\PartenaireController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowStepController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
| Authenticated app routes (auth + account.permanent) live in routes/authenticated.php.
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin', 'admin.include_soft_deleted'])
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/toggle-maintenance', [AdminController::class, 'toggleMaintenance'])->name('toggle.maintenance');

        Route::prefix('ocr-test')->name('ocr.')->controller(OcrTestController::class)->group(function () {
            Route::get('/', 'create')->name('test');
            Route::post('/', 'store')->name('test.store');
        });

        Route::prefix('email-test')->name('email.')->controller(EmailTestController::class)->group(function () {
            Route::get('/', 'create')->name('test');
            Route::post('/', 'store')->name('test.store');
        });

        Route::prefix('sms-test')->name('sms.')->controller(SmsTestController::class)->group(function () {
            Route::get('/', 'create')->name('test');
            Route::post('/', 'store')->name('test.store');
        });

        Route::prefix('queue')->name('queue.')->controller(QueueMonitorController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/failed/{uuid}', 'showFailed')->name('failed.show');
            Route::post('/failed/{uuid}/retry', 'retryFailed')->name('failed.retry');
            Route::delete('/failed/{uuid}', 'forgetFailed')->name('failed.forget');
            Route::post('/failed/retry-all', 'retryAllFailed')->name('failed.retry-all');
            Route::delete('/pending/{id}', 'destroyPending')->name('pending.destroy');
        });

        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::resource('users', UserController::class)->except(['destroy', 'show']);

        Route::prefix('users/{user}')->name('users.')->controller(UserController::class)->group(function () {
            Route::patch('/toggle-active', 'toggleActive')->name('toggle-active');
            Route::post('/reset-password', 'resetPassword')->name('reset-password');
        });

        Route::prefix('sessions')->name('sessions.')->controller(ActiveSessionController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::delete('/{session}', 'destroy')->name('destroy');
            Route::delete('/user/{user}', 'destroyUser')->name('destroy-user');
        });

        Route::post('carousels/reorder', [CarouselController::class, 'reorder'])->name('carousels.reorder');
        Route::resource('carousels', CarouselController::class)->except(['show']);

        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

        Route::prefix('roles')->name('roles.')->controller(RoleController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{role}', 'update')->name('update');
            Route::delete('/{role}', 'destroy')->name('destroy');
        });

        Route::prefix('permissions')->name('permissions.')->controller(PermissionController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{permission}', 'update')->name('update');
            Route::delete('/{permission}', 'destroy')->name('destroy');
        });

        Route::prefix('flux-transitions')->name('flux-transitions.')->controller(FluxTransitionController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/types', 'storeType')->name('types.store');
            Route::delete('/types/{typeCode}', 'destroyType')->where('typeCode', '[A-Z0-9_]+')->name('types.destroy');
            Route::post('/step-transitions', 'storeStepTransition')->name('step-transitions.store');
            Route::patch('/step-transitions/{stepTransition}', 'updateStepTransition')->name('step-transitions.update');
            Route::delete('/step-transitions/{stepTransition}', 'destroyStepTransition')->name('step-transitions.destroy');
            Route::post('/step-transitions/{stepTransition}/move-up', 'moveUpStepTransition')->name('step-transitions.move-up');
            Route::post('/step-transitions/{stepTransition}/move-down', 'moveDownStepTransition')->name('step-transitions.move-down');
            Route::post('/required', 'storeRequired')->name('required.store');
            Route::delete('/required/{requiredCircuitService}', 'destroyRequired')->name('required.destroy');
            Route::post('/sla', 'storeSla')->name('sla.store');
            Route::delete('/sla/{serviceSla}', 'destroySla')->name('sla.destroy');
        });

        Route::prefix('workflow-steps')->name('workflow-steps.')->controller(WorkflowStepController::class)->group(function () {
            Route::post('/', 'store')->name('store');
            Route::post('/{workflowStep}/clone', 'clone')->name('clone');
            Route::patch('/{workflowStep}', 'update')->name('update');
            Route::delete('/{workflowStep}', 'destroy')->name('destroy');
        });

        Route::prefix('documents/upload')->name('documents.')->controller(DocumentController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'upload')->name('upload');
        });

        Route::prefix('demandes')->name('demandes.')->controller(DemandeManagementController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{demande}', 'edit')->name('show');
            Route::post('/{demande}/update-status', 'updateStatus')->middleware('password.confirm')->name('updateStatus');
            Route::post('/{demande}/annuler', 'annuler')->middleware('password.confirm')->name('annuler');
        });

        Route::prefix('rapports')->name('reports.')->controller(ReportController::class)->group(function () {
            Route::get('/', 'adminIndex')->name('admin.index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{report}/edit', 'edit')->name('edit');
            Route::put('/{report}', 'update')->name('update');
            Route::delete('/{report}', 'destroy')->name('destroy');
            Route::post('/{report}/toggle', 'togglePublish')->name('toggle');
        });

        Route::prefix('actualites')->name('actualites.')->controller(ActualiteController::class)->group(function () {
            Route::get('/', 'adminIndex')->name('admin.index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{actualite}/edit', 'edit')->name('edit');
            Route::put('/{actualite}', 'update')->name('update');
            Route::delete('/{actualite}', 'destroy')->name('destroy');
            Route::post('/{actualite}/toggle', 'togglePublish')->name('toggle');
        });

        Route::prefix('newsletter')->name('newsletter.')->group(function () {
            Route::get('/', [NewsletterController::class, 'adminIndex'])->name('admin.index');
            Route::get('/export', [NewsletterController::class, 'export'])->name('export');
            Route::get('/compose', [NewsletterController::class, 'compose'])->name('compose');
            Route::post('/send', [NewsletterController::class, 'send'])->name('send');
            Route::delete('/{newsletter}', [NewsletterController::class, 'destroy'])->name('destroy');

            Route::controller(NewsletterSubscriberController::class)->group(function () {
                Route::get('/import', 'importForm')->name('import');
                Route::post('/import', 'import')->name('import.store');
            });

            Route::prefix('campaigns')->name('campaigns.')->controller(NewsletterCampaignController::class)->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{campaign}', 'show')->name('show');
                Route::get('/{campaign}/edit', 'edit')->name('edit');
                Route::put('/{campaign}', 'update')->name('update');
                Route::delete('/{campaign}', 'destroy')->name('destroy');
                Route::post('/{campaign}/duplicate', 'duplicate')->name('duplicate');
                Route::post('/{campaign}/test', 'test')->name('test');
                Route::post('/{campaign}/cancel', 'cancel')->name('cancel');
            });
        });

        Route::prefix('contacts')->name('contacts.')->controller(ContactController::class)->group(function () {
            Route::get('/', 'adminIndex')->name('index');
            Route::get('/{contact}', 'adminShow')->name('show');
            Route::post('/{contact}/en-cours', 'markEnCours')->name('markEnCours');
            Route::post('/{contact}/traite', 'markTraite')->name('markTraite');
            Route::post('/mark-all-traite', 'markAllTraite')->name('markAllTraite');
            Route::delete('/{contact}', 'adminDestroy')->name('destroy');
        });

        Route::prefix('faq')->name('faq.')->controller(FaqController::class)->group(function () {
            Route::get('/', 'adminIndex')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{faqItem}', 'update')->name('update');
            Route::delete('/{faqItem}', 'destroy')->name('destroy');
            Route::post('/{faqItem}/toggle', 'togglePublish')->name('toggle');
        });

        Route::prefix('glossaire')->name('glossaire.')->controller(GlossaireController::class)->group(function () {
            Route::get('/', 'adminIndex')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{glossaireTerm}', 'update')->name('update');
            Route::delete('/{glossaireTerm}', 'destroy')->name('destroy');
            Route::post('/{glossaireTerm}/toggle', 'togglePublish')->name('toggle');
        });

        Route::prefix('publications')->name('publications.')->controller(PublicationController::class)->group(function () {
            Route::get('/', 'adminIndex')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{publication}', 'update')->name('update');
            Route::delete('/{publication}', 'destroy')->name('destroy');
            Route::post('/{publication}/toggle', 'togglePublish')->name('toggle');
        });

        Route::prefix('publication-types')->name('publication-types.')->controller(PublicationController::class)->group(function () {
            Route::post('/', 'storeType')->name('store');
            Route::put('/{publicationType}', 'updateType')->name('update');
            Route::delete('/{publicationType}', 'destroyType')->name('destroy');
        });

        Route::prefix('mediatheque')->name('mediatheque.')->controller(MediathequeController::class)->group(function () {
            Route::get('/', 'adminIndex')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{mediathequeItem}', 'update')->name('update');
            Route::delete('/{mediathequeItem}', 'destroy')->name('destroy');
            Route::post('/{mediathequeItem}/toggle', 'togglePublish')->name('toggle');
        });

        Route::prefix('institution-images')->name('institution-images.')->controller(InstitutionImageController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::post('/reorder', 'reorder')->name('reorder');
            Route::get('/{institutionImage}/edit', 'edit')->name('edit');
            Route::put('/{institutionImage}', 'update')->name('update');
            Route::delete('/{institutionImage}', 'destroy')->name('destroy');
        });

        Route::prefix('officiels')->name('officials.')->controller(OfficialController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{official}/edit', 'edit')->name('edit');
            Route::put('/{official}', 'update')->name('update');
            Route::delete('/{official}', 'destroy')->name('destroy');
        });

        Route::prefix('directions')->name('directions.')->controller(DirectionDepartementaleController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{direction}', 'update')->name('update');
            Route::delete('/{direction}', 'destroy')->name('destroy');
        });

        Route::prefix('contact-parameters')->name('contact-parameters.')->controller(ContactParameterController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/', 'update')->name('update');
        });

        Route::prefix('rdv-settings')->name('rdv-settings.')->controller(RdvSettingsController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/', 'update')->name('update');
        });

        Route::prefix('contact-subjects')->name('contact-subjects.')->controller(ContactSubjectController::class)->group(function () {
            Route::post('/', 'store')->name('store');
            Route::put('/{contactSubject}', 'update')->name('update');
            Route::delete('/{contactSubject}', 'destroy')->name('destroy');
            Route::post('/{contactSubject}/toggle', 'toggle')->name('toggle');
        });

        Route::prefix('partenaires')->name('partenaires.')->controller(PartenaireController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::post('/reorder', 'reorder')->name('reorder');
            Route::get('/{partenaire}/edit', 'edit')->name('edit');
            Route::put('/{partenaire}', 'update')->name('update');
            Route::delete('/{partenaire}', 'destroy')->name('destroy');
        });
    });
