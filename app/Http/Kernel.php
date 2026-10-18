<?php

namespace App\Http;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\PersistSessionRequestMetadata;
use App\Http\Middleware\CorbeilleAccess;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnsureAccountFullyActive;
use App\Http\Middleware\EnsureNotAdmin;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsurePensionContext;
use App\Http\Middleware\EnsurePensionPortalAccess;
use App\Http\Middleware\EnsurePermanentAccount;
use App\Http\Middleware\MergeFormUploadStash;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RecordSiteVisit;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RequestLogger;
use App\Http\Middleware\RequireActiveAccount;
use App\Http\Middleware\RequirePasswordConfirmation;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\ValidateSignature;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            PersistSessionRequestMetadata::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            SetLocale::class,
            CheckMaintenanceMode::class,
            RecordSiteVisit::class,
            MergeFormUploadStash::class,
            EnsurePasswordChanged::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            ThrottleRequests::class.':api',
            SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'auth.session' => AuthenticateSession::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePasswordConfirmation::class,
        'precognitive' => HandlePrecognitiveRequests::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,

        // Spatie Permission
        'role' => RoleMiddleware::class,
        'permission' => PermissionMiddleware::class,
        'role_or_permission' => RoleOrPermissionMiddleware::class,
        'corbeille.access' => CorbeilleAccess::class,
        'not.admin' => EnsureNotAdmin::class,
        'account.full' => EnsureAccountFullyActive::class,
        'account.permanent' => EnsurePermanentAccount::class,
        'permanent.account' => RequireActiveAccount::class,
        'pension.context' => EnsurePensionContext::class,
        'pension.portal' => EnsurePensionPortalAccess::class,
        'request.log' => RequestLogger::class,
    ];
}
