<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\CivilStatus;
use App\Models\Gender;
use App\Models\Institution;
use App\Models\Service;
use App\Models\User;
use App\Models\UserCategory;
use App\Services\Account\AdminUserPasswordResetService;
use App\Services\Account\InstitutionAccountService;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\Admin\ActiveSessionService;
use App\Services\Admin\AdminUserColumnCatalog;
use App\Services\Admin\UserLoginStatisticsService;
use App\Services\Pension\BeneficiaireProvisioningService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Permission;
use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $columnCatalog = app(AdminUserColumnCatalog::class);
        $selectedColumns = $columnCatalog->resolveFromRequest($request);

        $users = $this->sortedUsersQuery($request)->paginate(15);
        if ($users instanceof AbstractPaginator) {
            $users->withQueryString();
        }

        $requestedSort = $request->string('sort')->toString();
        $requestedDirection = $request->input('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $requestedSort = ($requestedSort !== '' && $columnCatalog->isSortable($requestedSort))
            ? $requestedSort
            : null;

        $activeSort = $columnCatalog->resolveActiveSort($selectedColumns, $requestedSort, $requestedDirection);
        $sortColumn = $activeSort['sort_explicit'] ? $activeSort['sort_column'] : null;
        $sortDirection = $activeSort['sort_direction'];
        $displaySortColumn = $activeSort['sort_column'];
        $displaySortDirection = $activeSort['sort_direction'];
        $columnDefaultDirections = $columnCatalog->defaultDirectionsByKey();
        $roles = Role::orderBy('name')->get();
        $sessionCounts = app(ActiveSessionService::class)->authenticatedCountsByUserId();
        $sessionsManageable = app(ActiveSessionService::class)->isManageable();

        return view('admin.users.index', compact(
            'users',
            'roles',
            'sessionCounts',
            'sessionsManageable',
            'columnCatalog',
            'selectedColumns',
            'sortColumn',
            'sortDirection',
            'displaySortColumn',
            'displaySortDirection',
            'columnDefaultDirections',
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $columnCatalog = app(AdminUserColumnCatalog::class);
        $selectedColumns = $columnCatalog->resolveFromRequest($request);
        $filename = 'utilisateurs-'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($request, $columnCatalog, $selectedColumns) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv(
                $handle,
                array_map(fn (string $key) => $columnCatalog->label($key), $selectedColumns),
            );

            $this->sortedUsersQuery($request)
                ->chunk(200, function ($users) use ($handle, $columnCatalog, $selectedColumns) {
                    foreach ($users as $user) {
                        fputcsv(
                            $handle,
                            array_map(
                                fn (string $key) => $columnCatalog->exportValue($user, $key),
                                $selectedColumns,
                            ),
                        );
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $services = Service::orderBy('nom')->get();
        $genders = Gender::orderBy('name')->get();
        $civilStatuses = CivilStatus::orderBy('name')->get();
        $categories = UserCategory::orderedForAdmin();
        $institutions = Institution::query()
            ->whereDoesntHave('loginUser')
            ->orderBy('raison_sociale')
            ->get();

        $permissions = Permission::orderBy('name')->get();
        $roleOptions = $this->roleListboxOptions($roles);
        $permissionOptions = $this->permissionListboxOptions($permissions);
        $assignedRoles = array_values(array_filter((array) old('roles', [])));
        $assignedDirectPermissions = array_values(array_filter((array) old('permissions', [])));

        return view('admin.users.create', compact(
            'roles',
            'services',
            'genders',
            'civilStatuses',
            'categories',
            'institutions',
            'roleOptions',
            'permissionOptions',
            'assignedRoles',
            'assignedDirectPermissions',
        ));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->normalizedValidated();

        $roles = array_values(array_unique(array_filter($validated['roles'] ?? [])));
        $directPermissions = array_values(array_unique(array_filter($validated['permissions'] ?? [])));
        unset($validated['roles'], $validated['permissions'], $validated['primary_category']);

        if (empty($validated['username'])) {
            unset($validated['username']);
        }

        $validated['must_change_password'] = true;

        $user = User::create($validated);

        if ($roles !== []) {
            $user->syncRoles($roles);
        }

        $user->syncPermissions($directPermissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->syncAccountCategories($user, $request, $roles);

        if ($request->filled('institution_id')) {
            app(InstitutionAccountService::class)->ensureInstitutionLink(
                $user->fresh(),
                Institution::query()->findOrFail((int) $request->input('institution_id'))
            );
        }

        if ($user->hasCategory('pensionne')) {
            app(BeneficiaireProvisioningService::class)->ensureForUser($user->fresh());
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        $user->load(['categoryAssignments', 'roles', 'permissions', 'institution']);

        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        $services = Service::orderBy('nom')->get();
        $genders = Gender::orderBy('name')->get();
        $civilStatuses = CivilStatus::orderBy('name')->get();
        $categories = UserCategory::orderedForAdmin();
        $institutions = Institution::query()
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('loginUser')
                    ->orWhere('id', $user->institution_id);
            })
            ->orderBy('raison_sociale')
            ->get();

        $activeSessionService = app(ActiveSessionService::class);
        $loginStatistics = app(UserLoginStatisticsService::class);
        $userActiveSessions = $activeSessionService->list(userId: $user->id, authenticatedOnly: true)
            ->map(function (array $session) use ($loginStatistics) {
                $session['location_label'] = $loginStatistics->locationLabelForIp($session['ip_address'] ?? null);

                return $session;
            });
        $sessionsManageable = $activeSessionService->isManageable();
        $connectionTimeline = $loginStatistics->connectionTimelineForUser($user->id, 25);

        $storedPrimary = $user->categoryAssignments->firstWhere('is_primary', true)?->user_category_code
            ?? $user->categoryAssignments->first()?->user_category_code;
        $accountCategory = old('primary_category', $storedPrimary);
        $roleOptions = $this->roleListboxOptions($roles);
        $permissionOptions = $this->permissionListboxOptions($permissions);
        $assignedRoles = array_values(array_filter((array) old('roles', $user->roles->pluck('name')->all())));
        $assignedDirectPermissions = array_values(array_filter((array) old(
            'permissions',
            $user->permissions->pluck('name')->all(),
        )));

        return view('admin.users.edit', compact(
            'user',
            'roles',
            'services',
            'genders',
            'civilStatuses',
            'categories',
            'institutions',
            'userActiveSessions',
            'sessionsManageable',
            'connectionTimeline',
            'accountCategory',
            'roleOptions',
            'permissionOptions',
            'assignedRoles',
            'assignedDirectPermissions',
        ));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->normalizedValidated();

        $roles = array_values(array_unique(array_filter($validated['roles'] ?? [])));
        $directPermissions = array_values(array_unique(array_filter($validated['permissions'] ?? [])));
        unset($validated['roles'], $validated['permissions'], $validated['primary_category']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['must_change_password'] = true;
        }

        if (empty($validated['username'])) {
            unset($validated['username']);
        }

        $user->update($validated);

        $user->syncRoles($roles);
        $user->syncPermissions($directPermissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->syncAccountCategories($user, $request, $roles);

        if ($request->filled('institution_id')) {
            app(InstitutionAccountService::class)->ensureInstitutionLink(
                $user->fresh(),
                Institution::query()->findOrFail((int) $request->input('institution_id'))
            );
        } elseif ($user->hasCategory('institution') && ! $request->filled('institution_id')) {
            // conserver le lien institution existant
        }

        if ($user->hasCategory('pensionne')) {
            app(BeneficiaireProvisioningService::class)->ensureForUser($user->fresh());
        }

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'Modifications enregistrées.');
    }

    public function resetPassword(User $user, AdminUserPasswordResetService $passwordReset)
    {
        try {
            $passwordReset->resetAndNotify($user, auth()->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('admin.users.edit', $user)
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('admin.users.edit', $user)
            ->with(
                'success',
                "Mot de passe réinitialisé. Un e-mail a été envoyé à {$user->email}."
            );
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->toggleAdministrativeAccess();
        $user->refresh();

        $label = $user->allowsLogin() ? 'activé' : 'désactivé';

        return back()->with('success', "Le compte de {$user->name} a été {$label}.");
    }

    private function filteredUsersQuery(Request $request): Builder
    {
        $query = User::query()
            ->with(['roles', 'service', 'categoryAssignments.category', 'institution', 'gender', 'civilStatus']);

        if ($request->filled('q')) {
            $search = (string) $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('username', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('role')) {
            $query->role((string) $request->input('role'));
        }

        return $query;
    }

    private function sortedUsersQuery(Request $request): Builder
    {
        $query = $this->filteredUsersQuery($request);
        $columnCatalog = app(AdminUserColumnCatalog::class);
        $sort = $request->string('sort')->toString();
        $direction = $request->input('dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $selectedColumns = $columnCatalog->resolveFromRequest($request);
        $requestedSort = ($sort !== '' && $columnCatalog->isSortable($sort)) ? $sort : null;
        $activeSort = $columnCatalog->resolveActiveSort($selectedColumns, $requestedSort, $direction);

        $columnCatalog->applySort(
            $query,
            $activeSort['sort_column'],
            $activeSort['sort_direction'],
        );

        return $query;
    }

    /**
     * @param  Collection<int, Role>  $roles
     * @return list<array{value: string, label: string, description: ?string}>
     */
    private function roleListboxOptions($roles): array
    {
        return $roles->map(function (Role $role) {
            return [
                'value' => $role->name,
                'label' => $role->name,
                'description' => filled($role->description) ? $role->description : null,
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, Permission>  $permissions
     * @return list<array{value: string, label: string, description: ?string}>
     */
    private function permissionListboxOptions($permissions): array
    {
        return $permissions->map(function (Permission $permission) {
            return [
                'value' => $permission->name,
                'label' => $permission->name,
                'description' => filled($permission->description) ? $permission->description : null,
            ];
        })->values()->all();
    }

    /**
     * @param  list<string>  $roles
     */
    private function syncAccountCategories(User $user, Request $request, array $roles): void
    {
        $code = $this->resolveAccountCategoryCode($request, $roles);

        if ($code === null) {
            return;
        }

        app(UserCategoryRoleSyncService::class)->syncSingleCategory($user->fresh(), $code);
    }

    /**
     * Un compte = une catégorie user_categories ; les rôles restent multiples.
     *
     * @param  list<string>  $roles
     */
    private function resolveAccountCategoryCode(Request $request, array $roles): ?string
    {
        $validCodes = UserCategory::configuredCodes();
        $primaryFromForm = $request->input('primary_category');

        if (filled($primaryFromForm) && in_array($primaryFromForm, $validCodes, true)) {
            return $primaryFromForm;
        }

        if ($request->filled('institution_id') || in_array('institution', $roles, true)) {
            return 'institution';
        }

        $internalRoles = RolesAndPermissionsSeeder::internalRoles();
        if ($request->filled('service_id') || array_intersect($roles, $internalRoles) !== []) {
            return 'personnel_dpc';
        }

        foreach (['pensionne', 'fonctionnaire', 'externe'] as $code) {
            if (in_array($code, $roles, true)) {
                return $code;
            }
        }

        foreach ($roles as $role) {
            if (in_array($role, $validCodes, true)) {
                return $role;
            }
        }

        return null;
    }
}
