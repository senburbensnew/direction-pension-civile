<?php

namespace App\Http\Controllers;

use App\Enums\BeneficiaireRelationEnum;
use App\Models\Beneficiaire;
use App\Models\Gender;
use App\Models\Institution;
use App\Models\Service;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Models\UserCategory;
use App\Rules\Telephone;
use App\Services\Account\InstitutionAccountService;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\Admin\ActiveSessionService;
use App\Services\Pension\BeneficiaireProvisioningService;
use App\Services\Pension\UserBeneficiaireLinkService;
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
        $categories = UserCategory::orderBy('label')->get();
        $institutions = Institution::query()
            ->whereDoesntHave('loginUser')
            ->orderBy('raison_sociale')
            ->get();

        return view('admin.users.create', compact('roles', 'services', 'genders', 'categories', 'institutions'));
    }

    public function store(Request $request)
    {
        $categoryCodes = array_keys(config('account_categories.categories', []));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'nullable|string|min:3|max:50|regex:/^[A-Za-z0-9._-]+$/|unique:users,username',
            'phone' => ['nullable', 'string', 'max:20', new Telephone],
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name',
            'service_id' => 'nullable|integer|exists:services,id',
            'gender_id' => 'nullable|integer|exists:genders,id',
            'category_codes' => 'nullable|array',
            'category_codes.*' => ['string', Rule::in($categoryCodes)],
            'primary_category' => ['nullable', 'string', Rule::in($categoryCodes)],
            'institution_id' => 'nullable|integer|exists:institutions,id|unique:users,institution_id',
            'pension_code' => 'nullable|string|max:50',
            'nif' => 'nullable|string|max:20',
            'ninu' => 'nullable|string|max:20',
        ]);

        $role = $validated['role'] ?? null;
        unset($validated['role'], $validated['category_codes'], $validated['primary_category']);

        $pensionCode = filled($validated['pension_code'] ?? null)
            ? strtoupper(trim((string) $validated['pension_code']))
            : null;
        unset($validated['pension_code']);

        if (empty($validated['username'])) {
            unset($validated['username']);
        }

        if (empty($validated['gender_id'])) {
            $validated['gender_id'] = null;
        }

        $this->validateRoleServiceRequirement($role, $validated['service_id'] ?? null);

        $validated['must_change_password'] = true;

        $user = User::create($validated);

        if ($role) {
            $user->assignRole($role);
        }

        $this->syncAccountCategories($user, $request, $role);

        if ($request->filled('institution_id')) {
            app(InstitutionAccountService::class)->ensureInstitutionLink(
                $user->fresh(),
                Institution::query()->findOrFail((int) $request->input('institution_id'))
            );
        }

        if ($user->hasPensionPortalAccess() && $pensionCode) {
            app(BeneficiaireProvisioningService::class)->provisionAdultSelf($user->fresh(), null, $pensionCode);
        } elseif ($user->hasCategory('pensionne')) {
            app(BeneficiaireProvisioningService::class)->ensureForUser($user->fresh());
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        $user->load(['categoryAssignments', 'roles', 'institution', 'beneficiaireLinks.beneficiaire.affiliations']);

        $roles = Role::orderBy('name')->get();
        $services = Service::orderBy('nom')->get();
        $genders = Gender::orderBy('name')->get();
        $categories = UserCategory::orderBy('label')->get();
        $institutions = Institution::query()
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('loginUser')
                    ->orWhere('id', $user->institution_id);
            })
            ->orderBy('raison_sociale')
            ->get();

        $beneficiaires = Beneficiaire::query()
            ->with('affiliations')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $activeSessionService = app(ActiveSessionService::class);
        $userActiveSessions = $activeSessionService->list(userId: $user->id, authenticatedOnly: true);
        $sessionsManageable = $activeSessionService->isManageable();

        return view('admin.users.edit', compact(
            'user',
            'roles',
            'services',
            'genders',
            'categories',
            'institutions',
            'beneficiaires',
            'userActiveSessions',
            'sessionsManageable',
        ));
    }

    public function update(Request $request, User $user)
    {
        $categoryCodes = array_keys(config('account_categories.categories', []));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'username' => 'nullable|string|min:3|max:50|regex:/^[A-Za-z0-9._-]+$/|unique:users,username,'.$user->id,
            'phone' => ['nullable', 'string', 'max:20', new Telephone],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name',
            'service_id' => 'nullable|integer|exists:services,id',
            'gender_id' => 'nullable|integer|exists:genders,id',
            'category_codes' => 'nullable|array',
            'category_codes.*' => ['string', Rule::in($categoryCodes)],
            'primary_category' => ['nullable', 'string', Rule::in($categoryCodes)],
            'institution_id' => [
                'nullable',
                'integer',
                Rule::exists('institutions', 'id'),
                Rule::unique('users', 'institution_id')->ignore($user->id),
            ],
            'pension_code' => 'nullable|string|max:50',
            'nif' => 'nullable|string|max:20',
            'ninu' => 'nullable|string|max:20',
        ]);

        $role = $validated['role'] ?? null;
        unset($validated['role'], $validated['category_codes'], $validated['primary_category']);

        $pensionCode = filled($validated['pension_code'] ?? null)
            ? strtoupper(trim((string) $validated['pension_code']))
            : null;
        unset($validated['pension_code']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['must_change_password'] = true;
        }

        if (empty($validated['username'])) {
            unset($validated['username']);
        }

        if (empty($validated['gender_id'])) {
            $validated['gender_id'] = null;
        }

        $this->validateRoleServiceRequirement($role, $validated['service_id'] ?? null);

        $user->update($validated);

        $user->syncRoles($role ? [$role] : []);

        $this->syncAccountCategories($user, $request, $role);

        if ($request->filled('institution_id')) {
            app(InstitutionAccountService::class)->ensureInstitutionLink(
                $user->fresh(),
                Institution::query()->findOrFail((int) $request->input('institution_id'))
            );
        } elseif ($user->hasCategory('institution') && ! $request->filled('institution_id')) {
            // conserver le lien institution existant
        }

        if ($user->hasPensionPortalAccess() && $pensionCode) {
            app(BeneficiaireProvisioningService::class)->provisionAdultSelf($user->fresh(), null, $pensionCode);
        } elseif ($user->hasCategory('pensionne')) {
            app(BeneficiaireProvisioningService::class)->ensureForUser($user->fresh());
        }

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'Modifications enregistrées.');
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

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé.');
    }

    public function storeBeneficiaireLink(Request $request, User $user)
    {
        $validated = $request->validate([
            'beneficiaire_id' => 'required|integer|exists:beneficiaires,id',
            'relation' => 'required|string|in:'.implode(',', array_column(BeneficiaireRelationEnum::cases(), 'value')),
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $beneficiaire = Beneficiaire::query()->findOrFail((int) $validated['beneficiaire_id']);
        $relation = BeneficiaireRelationEnum::from($validated['relation']);

        app(UserBeneficiaireLinkService::class)->attach(
            $user,
            $beneficiaire,
            $relation,
            isset($validated['valid_from']) ? new \DateTimeImmutable($validated['valid_from']) : null,
            isset($validated['valid_until']) ? new \DateTimeImmutable($validated['valid_until']) : null,
        );

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'Lien bénéficiaire ajouté.');
    }

    public function destroyBeneficiaireLink(User $user, UserBeneficiaireLink $beneficiaireLink)
    {
        if ((int) $beneficiaireLink->user_id !== (int) $user->id) {
            abort(404);
        }

        app(UserBeneficiaireLinkService::class)->detach($beneficiaireLink);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'Lien bénéficiaire retiré.');
    }

    private function validateRoleServiceRequirement(?string $role, mixed $serviceId): void
    {
        if (! $role) {
            return;
        }

        $requiresService = RolesAndPermissionsSeeder::rolesRequiringService();

        if (in_array($role, $requiresService, true) && blank($serviceId)) {
            throw ValidationException::withMessages([
                'service_id' => 'Ce rôle exige un service de rattachement.',
            ]);
        }
    }

    private function syncAccountCategories(User $user, Request $request, ?string $role): void
    {
        $sync = app(UserCategoryRoleSyncService::class);

        $codes = $request->input('category_codes');

        if (! is_array($codes) || $codes === []) {
            $codes = array_filter([$role]);
        }

        if ($request->filled('service_id') && ! in_array('personnel_dpc', $codes, true)) {
            $codes[] = 'personnel_dpc';
        }

        $codes = array_values(array_unique(array_filter($codes)));

        if ($codes === []) {
            return;
        }

        $primary = $request->input('primary_category') ?: $codes[0];

        if ($request->filled('service_id') && in_array('personnel_dpc', $codes, true)) {
            $primary = $request->input('primary_category') ?: 'personnel_dpc';
        }

        $sync->syncCategories($user->fresh(), $codes, $primary);
    }
}
