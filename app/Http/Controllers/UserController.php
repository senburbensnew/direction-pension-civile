<?php

namespace App\Http\Controllers;

use App\Models\Gender;
use App\Models\Institution;
use App\Models\Service;
use App\Models\User;
use App\Models\UserCategory;
use App\Rules\Nif;
use App\Rules\Ninu;
use App\Rules\Telephone;
use App\Services\Account\AdminUserPasswordResetService;
use App\Services\Account\InstitutionAccountService;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\Admin\ActiveSessionService;
use App\Services\Admin\UserLoginStatisticsService;
use App\Services\Pension\BeneficiaireProvisioningService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'service', 'categoryAssignments.category', 'institution']);

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('email', 'like', '%'.$request->q.'%')
                    ->orWhere('username', 'like', '%'.$request->q.'%');
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->paginate(15);
        $roles = Role::orderBy('name')->get();
        $sessionCounts = app(ActiveSessionService::class)->authenticatedCountsByUserId();
        $sessionsManageable = app(ActiveSessionService::class)->isManageable();

        return view('admin.users.index', compact('users', 'roles', 'sessionCounts', 'sessionsManageable'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $services = Service::orderBy('nom')->get();
        $genders = Gender::orderBy('name')->get();
        $categories = UserCategory::orderedForAdmin();
        $institutions = Institution::query()
            ->whereDoesntHave('loginUser')
            ->orderBy('raison_sociale')
            ->get();

        $roleOptions = $this->roleListboxOptions($roles);
        $assignedRoles = array_values(array_filter((array) old('roles', [])));

        return view('admin.users.create', compact(
            'roles',
            'services',
            'genders',
            'categories',
            'institutions',
            'roleOptions',
            'assignedRoles',
        ));
    }

    public function store(Request $request)
    {
        $this->mergeDefaultDisplayName($request);

        $validated = $request->validate(array_merge(
            $this->userFormValidationRules($request),
            ['password' => 'required|string|min:8|confirmed'],
        ));

        $roles = array_values(array_unique(array_filter($validated['roles'] ?? [])));
        unset($validated['roles'], $validated['primary_category']);

        if (empty($validated['username'])) {
            unset($validated['username']);
        }

        $this->normalizeValidatedUserFields($validated);

        $this->validateRolesServiceRequirement($roles, $validated['service_id'] ?? null);

        $validated['must_change_password'] = true;

        $user = User::create($validated);

        if ($roles !== []) {
            $user->syncRoles($roles);
        }

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
        $user->load(['categoryAssignments', 'roles', 'institution']);

        $roles = Role::orderBy('name')->get();
        $services = Service::orderBy('nom')->get();
        $genders = Gender::orderBy('name')->get();
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
        $assignedRoles = array_values(array_filter((array) old('roles', $user->roles->pluck('name')->all())));

        return view('admin.users.edit', compact(
            'user',
            'roles',
            'services',
            'genders',
            'categories',
            'institutions',
            'userActiveSessions',
            'sessionsManageable',
            'connectionTimeline',
            'accountCategory',
            'roleOptions',
            'assignedRoles',
        ));
    }

    public function update(Request $request, User $user)
    {
        $this->mergeDefaultDisplayName($request);

        $validated = $request->validate(array_merge(
            $this->userFormValidationRules($request, $user),
            ['password' => 'nullable|string|min:8|confirmed'],
        ));

        $roles = array_values(array_unique(array_filter($validated['roles'] ?? [])));
        unset($validated['roles'], $validated['primary_category']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['must_change_password'] = true;
        }

        if (empty($validated['username'])) {
            unset($validated['username']);
        }

        $this->normalizeValidatedUserFields($validated);

        $this->validateRolesServiceRequirement($roles, $validated['service_id'] ?? null);

        $user->update($validated);

        $user->syncRoles($roles);

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

    /**
     * @param  \Illuminate\Support\Collection<int, Role>  $roles
     * @return list<array{value: string, label: string}>
     */
    private function roleListboxOptions($roles): array
    {
        return $roles->map(fn (Role $role) => [
            'value' => $role->name,
            'label' => $role->name,
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function userFormValidationRules(Request $request, ?User $user = null): array
    {
        $categoryCodes = array_keys(config('account_categories.categories', []));
        $userId = $user?->id;

        return [
            'name' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                new Telephone,
                function (string $attribute, mixed $value, \Closure $fail) use ($userId): void {
                    if (blank($value)) {
                        return;
                    }

                    $existing = User::findByTelephone((string) $value);
                    if ($existing !== null && $existing->id !== $userId) {
                        $fail('Ce numéro de téléphone est déjà utilisé.');
                    }
                },
            ],
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
            'service_id' => 'nullable|integer|exists:services,id',
            'gender_id' => 'required|integer|exists:genders,id',
            'primary_category' => ['nullable', 'string', Rule::in($categoryCodes)],
            'institution_id' => [
                Rule::requiredIf(fn () => $request->input('primary_category') === 'institution'),
                'nullable',
                'integer',
                Rule::exists('institutions', 'id'),
                Rule::unique('users', 'institution_id')->ignore($userId),
            ],
            'nif' => [
                'nullable',
                'string',
                'max:20',
                new Nif,
                function (string $attribute, mixed $value, \Closure $fail) use ($userId): void {
                    if (blank($value)) {
                        return;
                    }

                    $existing = User::findByNif((string) $value);
                    if ($existing !== null && $existing->id !== $userId) {
                        $fail('Ce NIF est déjà utilisé.');
                    }
                },
            ],
            'ninu' => [
                'nullable',
                'string',
                'max:20',
                new Ninu,
                function (string $attribute, mixed $value, \Closure $fail) use ($userId): void {
                    if (blank($value)) {
                        return;
                    }

                    $existing = User::findByNinu((string) $value);
                    if ($existing !== null && $existing->id !== $userId) {
                        $fail('Ce NINU est déjà utilisé.');
                    }
                },
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function normalizeValidatedUserFields(array &$validated): void
    {
        if (isset($validated['phone']) && is_string($validated['phone'])) {
            $validated['phone'] = Telephone::normalize($validated['phone']);
        }

        foreach (['nif', 'ninu'] as $field) {
            if (blank($validated[$field] ?? null)) {
                $validated[$field] = null;
            }
        }
    }

    private function mergeDefaultDisplayName(Request $request): void
    {
        if ($request->filled('name')) {
            return;
        }

        $composed = $this->composeDisplayName(
            $request->input('firstname'),
            $request->input('lastname'),
        );

        if ($composed !== '') {
            $request->merge(['name' => $composed]);
        }
    }

    private function composeDisplayName(mixed $firstname, mixed $lastname): string
    {
        return trim(implode(' ', array_filter([
            is_string($firstname) ? trim($firstname) : '',
            is_string($lastname) ? trim($lastname) : '',
        ], fn (string $part) => $part !== '')));
    }

    /**
     * @param  list<string>  $roles
     */
    private function validateRolesServiceRequirement(array $roles, mixed $serviceId): void
    {
        if ($roles === []) {
            return;
        }

        $requiresService = RolesAndPermissionsSeeder::rolesRequiringService();

        foreach ($roles as $role) {
            if (in_array($role, $requiresService, true) && blank($serviceId)) {
                throw ValidationException::withMessages([
                    'service_id' => 'Ce rôle exige un service de rattachement.',
                ]);
            }
        }
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
