<?php

namespace App\Services\Admin;

use App\Models\CivilStatus;
use App\Models\Gender;
use App\Models\Institution;
use App\Models\Service;
use App\Models\User;
use App\Models\UserCategoryAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Role;

class AdminUserColumnCatalog
{
    /** @var list<string> */
    public const DEFAULT_COLUMNS = [
        'user_summary',
        'primary_category',
        'service',
        'roles',
        'status_label',
    ];

    public const DEFAULT_SORT_COLUMN = 'user_summary';

    public const DEFAULT_SORT_DIRECTION = 'asc';

    /**
     * @return list<string>
     */
    public function descendingDefaultSortKeys(): array
    {
        return [
            'birth_date',
            'created_at',
            'updated_at',
            'email_verified_at',
            'password_changed_at',
        ];
    }

    public function defaultDirectionFor(string $key): string
    {
        return in_array($key, $this->descendingDefaultSortKeys(), true) ? 'desc' : 'asc';
    }

    /**
     * @return array<string, string>
     */
    public function defaultDirectionsByKey(): array
    {
        $directions = [];

        foreach ($this->sortableKeys() as $key) {
            $directions[$key] = $this->defaultDirectionFor($key);
        }

        return $directions;
    }

    /**
     * @param  list<string>  $selectedColumns
     * @return array{sort_column: string, sort_direction: string, sort_explicit: bool}
     */
    public function resolveActiveSort(array $selectedColumns, ?string $sortColumn, string $sortDirection): array
    {
        if ($sortColumn !== null && $this->isSortable($sortColumn)) {
            return [
                'sort_column' => $sortColumn,
                'sort_direction' => $sortDirection === 'desc' ? 'desc' : 'asc',
                'sort_explicit' => true,
            ];
        }

        $fallbackColumn = in_array(self::DEFAULT_SORT_COLUMN, $selectedColumns, true)
            ? self::DEFAULT_SORT_COLUMN
            : $this->firstSortableKey($selectedColumns);

        if ($fallbackColumn === null) {
            $fallbackColumn = self::DEFAULT_SORT_COLUMN;
        }

        return [
            'sort_column' => $fallbackColumn,
            'sort_direction' => $this->defaultDirectionFor($fallbackColumn),
            'sort_explicit' => false,
        ];
    }

    /**
     * @param  list<string>  $selectedColumns
     */
    public function firstSortableKey(array $selectedColumns): ?string
    {
        foreach ($selectedColumns as $key) {
            if ($this->isSortable($key)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @return array<string, array{label: string, source: string}>
     */
    public function definitions(): array
    {
        return [
            'user_summary' => [
                'label' => 'Utilisateur',
                'source' => 'composite',
            ],
            'firstname' => [
                'label' => 'Prénom',
                'source' => 'users.firstname',
            ],
            'lastname' => [
                'label' => 'Nom',
                'source' => 'users.lastname',
            ],
            'username' => [
                'label' => "Nom d'utilisateur",
                'source' => 'users.username',
            ],
            'email' => [
                'label' => 'E-mail',
                'source' => 'users.email',
            ],
            'phone' => [
                'label' => 'Téléphone',
                'source' => 'users.phone',
            ],
            'nif' => [
                'label' => 'NIF',
                'source' => 'users.nif',
            ],
            'ninu' => [
                'label' => 'NINU',
                'source' => 'users.ninu',
            ],
            'birth_date' => [
                'label' => 'Date de naissance',
                'source' => 'users.birth_date',
            ],
            'address' => [
                'label' => 'Adresse',
                'source' => 'users.address',
            ],
            'email_verified_at' => [
                'label' => 'E-mail vérifié le',
                'source' => 'users.email_verified_at',
            ],
            'must_change_password' => [
                'label' => 'Changement de mot de passe requis',
                'source' => 'users.must_change_password',
            ],
            'password_changed_at' => [
                'label' => 'Mot de passe modifié le',
                'source' => 'users.password_changed_at',
            ],
            'primary_category' => [
                'label' => 'Type de compte',
                'source' => 'computed',
            ],
            'service' => [
                'label' => 'Service',
                'source' => 'relations.service',
            ],
            'institution' => [
                'label' => 'Institution',
                'source' => 'relations.institution',
            ],
            'gender' => [
                'label' => 'Sexe',
                'source' => 'relations.gender',
            ],
            'civil_status' => [
                'label' => 'État civil',
                'source' => 'relations.civil_status',
            ],
            'roles' => [
                'label' => 'Rôles',
                'source' => 'relations.roles',
            ],
            'status_label' => [
                'label' => 'Statut',
                'source' => 'computed',
            ],
            'created_at' => [
                'label' => 'Créé le',
                'source' => 'users.created_at',
            ],
            'updated_at' => [
                'label' => 'Modifié le',
                'source' => 'users.updated_at',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function optionalColumnKeys(): array
    {
        return array_values(array_diff(array_keys($this->definitions()), self::DEFAULT_COLUMNS));
    }

    /**
     * @param  list<string>|null  $requested
     * @return list<string>
     */
    public function resolveSelected(?array $requested): array
    {
        $allowed = array_keys($this->definitions());

        if (! is_array($requested) || $requested === []) {
            return self::DEFAULT_COLUMNS;
        }

        $selected = [];

        foreach ($requested as $key) {
            if (! is_string($key) || ! in_array($key, $allowed, true)) {
                continue;
            }

            if (! in_array($key, $selected, true)) {
                $selected[] = $key;
            }
        }

        if ($selected === []) {
            return self::DEFAULT_COLUMNS;
        }

        return $selected;
    }

    /**
     * @return list<string>
     */
    public function resolveFromRequest(Request $request): array
    {
        return $this->resolveSelected($request->input('columns'));
    }

    /**
     * @return list<string>
     */
    public function sortableKeys(): array
    {
        return array_keys($this->definitions());
    }

    public function isSortable(string $key): bool
    {
        return in_array($key, $this->sortableKeys(), true);
    }

    /**
     * @return array<string, string>
     */
    public function sortableColumnMap(): array
    {
        return [
            'user_summary' => 'name',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'username' => 'username',
            'email' => 'email',
            'phone' => 'phone',
            'nif' => 'nif',
            'ninu' => 'ninu',
            'birth_date' => 'birth_date',
            'address' => 'address',
            'email_verified_at' => 'email_verified_at',
            'must_change_password' => 'must_change_password',
            'password_changed_at' => 'password_changed_at',
            'status_label' => 'account_status',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
    }

    public function applySort(Builder $query, string $key, string $direction = 'asc'): void
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        if (isset($this->sortableColumnMap()[$key])) {
            $query->orderBy($this->sortableColumnMap()[$key], $direction);

            return;
        }

        match ($key) {
            'service' => $query->orderBy(
                Service::query()->select('nom')->whereColumn('services.id', 'users.service_id'),
                $direction,
            ),
            'institution' => $query->orderBy(
                Institution::query()->select('raison_sociale')->whereColumn('institutions.id', 'users.institution_id'),
                $direction,
            ),
            'gender' => $query->orderBy(
                Gender::query()->select('name')->whereColumn('genders.id', 'users.gender_id'),
                $direction,
            ),
            'civil_status' => $query->orderBy(
                CivilStatus::query()->select('name')->whereColumn('civil_statuses.id', 'users.civil_status_id'),
                $direction,
            ),
            'primary_category' => $query->orderBy(
                UserCategoryAssignment::query()
                    ->select('user_category_code')
                    ->whereColumn('user_category_assignments.user_id', 'users.id')
                    ->orderByDesc('is_primary')
                    ->orderBy('user_category_code')
                    ->limit(1),
                $direction,
            ),
            'roles' => $query->orderBy(
                Role::query()
                    ->select('roles.name')
                    ->join(
                        config('permission.table_names.model_has_roles').' as model_has_roles',
                        'roles.id',
                        '=',
                        'model_has_roles.role_id',
                    )
                    ->whereColumn('model_has_roles.model_id', 'users.id')
                    ->where('model_has_roles.model_type', User::class)
                    ->orderBy('roles.name')
                    ->limit(1),
                $direction,
            ),
            default => $query->orderBy('name', $direction),
        };
    }

    public function label(string $key): string
    {
        return $this->definitions()[$key]['label'] ?? $key;
    }

    public function displayValue(User $user, string $key): string
    {
        $value = $this->rawValue($user, $key);

        if ($value === null || $value === '') {
            return '—';
        }

        return (string) $value;
    }

    public function exportValue(User $user, string $key): string
    {
        if ($key === 'user_summary') {
            $email = filled($user->email) ? " ({$user->email})" : '';

            return trim((string) $user->name.$email);
        }

        $value = $this->rawValue($user, $key);

        return $value === null ? '' : (string) $value;
    }

    private function rawValue(User $user, string $key): mixed
    {
        return match ($key) {
            'user_summary' => $user->name,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'nif' => $user->nif,
            'ninu' => $user->ninu,
            'birth_date' => $user->birth_date?->format('d/m/Y'),
            'address' => $user->address,
            'email_verified_at' => $user->email_verified_at?->format('d/m/Y H:i'),
            'must_change_password' => $user->must_change_password ? 'Oui' : 'Non',
            'password_changed_at' => $user->password_changed_at?->format('d/m/Y H:i'),
            'primary_category' => $user->primaryCategoryCode(),
            'service' => $user->service?->nom,
            'institution' => $user->institution?->raison_sociale,
            'gender' => $user->gender?->name,
            'civil_status' => $user->civilStatus?->name,
            'roles' => $user->roles->pluck('name')->implode(', '),
            'status_label' => $user->resolvedAccountStatus()->label(),
            'created_at' => $user->created_at?->format('d/m/Y H:i'),
            'updated_at' => $user->updated_at?->format('d/m/Y H:i'),
            default => null,
        };
    }
}
