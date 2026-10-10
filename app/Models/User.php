<?php

namespace App\Models;

use App\Enums\AccountStatusEnum;
use App\Models\Gender;
use App\Models\Service;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Services\Account\UserCategoryResolver;
use App\Services\Pension\ActivePensionContext;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    public const ROLE_DIRECTION = 'direction';
    public const ROLE_DIRECTEUR = 'directeur';
    public const ROLE_ASSISTANT_DIRECTEUR = 'assistant_directeur';
    public const ROLE_AGENT_RDV = 'agent_rdv';
    public const ROLE_VALIDATEUR_RDV = 'validateur_rdv';
    public const ROLE_AGENT_FORMALITES = 'agent_formalites';

    /** @deprecated Use AccountStatusEnum::ACTIF */
    public const STATUS_ACTIF = AccountStatusEnum::ACTIF->value;

    /** @deprecated Use AccountStatusEnum::PROVISOIRE */
    public const STATUS_EN_ATTENTE_VALIDATION = AccountStatusEnum::PROVISOIRE->value;

    public const STATUS_PROVISOIRE = AccountStatusEnum::PROVISOIRE->value;

    /** @var list<string> */
    public const DIRECTION_ROLES = [
        self::ROLE_DIRECTION,
        self::ROLE_DIRECTEUR,
        self::ROLE_ASSISTANT_DIRECTEUR,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'firstname',
        'lastname',
        'username',
        'email',
        'phone',
        'password',
        'nif',
        'ninu',
        'service_id',
        'institution_id',
        'account_status',
        'gender_id',
        'profile_photo',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'account_status' => AccountStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (blank($user->username)) {
                $user->username = self::uniqueUsernameFrom($user->email ?: $user->name);
            } else {
                $user->username = self::normalizeUsername((string) $user->username);
            }
        });

        static::updating(function (self $user) {
            if ($user->isDirty('username') && filled($user->username)) {
                $user->username = self::normalizeUsername((string) $user->username);
            }
        });
    }

    public static function normalizeUsername(string $username): string
    {
        return strtolower(trim($username));
    }

    public static function uniqueUsernameFrom(string $source): string
    {
        /*
        |--------------------------------------------------------------------------
        | Normalisation
        |--------------------------------------------------------------------------
        | Exemple :
        |
        | " Jean-Pierre " -> "jean.pierre"
        | "Jean Pierre"   -> "jean.pierre"
        | "JEAN PIERRE"   -> "jean.pierre"
        */
        $base = Str::of($source)
            ->trim()
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '.')
            ->trim('.')
            ->value();

        /*
        |--------------------------------------------------------------------------
        | Sécurité : source vide
        |--------------------------------------------------------------------------
        */
        if ($base === '') {
            $base = 'pensionne';
        }

        /*
        |--------------------------------------------------------------------------
        | Premier username disponible
        |--------------------------------------------------------------------------
        */
        if (! self::where('username', $base)->exists()) {
            return $base;
        }

        /*
        |--------------------------------------------------------------------------
        | Username déjà utilisé
        |--------------------------------------------------------------------------
        |
        | jean.pierre
        | jean.pierre2
        | jean.pierre3
        | jean.pierre4
        | ...
        */
        $suffix = 2;

        do {
            $username = $base . $suffix;

            $exists = self::where('username', $username)->exists();

            if (! $exists) {
                return $username;
            }

            $suffix++;
        } while (true);
    }

    public static function normalizeDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    /**
     * Resolve a user for authentication or password reset.
     *
     * Accepted identifiers: phone, email, pension code, NIF — not username.
     */
    public static function findForLogin(string $identifier): ?self
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        /*
        * 1. Téléphone (format international obligatoire : +indicatif…)
        */
        $user = self::findByTelephone($identifier);

        if ($user) {
            return $user;
        }

        /*
        * 2. Email
        */
        if (str_contains($identifier, '@')) {
            return static::query()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($identifier)])
                ->first();
        }

        /*
        * 3. Normalisation des identifiants numériques
        */
        $compact = strtoupper(
            preg_replace('/\s+/', '', $identifier) ?? ''
        );

        $digits = self::normalizeDigits($identifier);

        /*
        * 4. Code pension (aligné sur App\Rules\CodePension)
        *
        * Exemples :
        * 8-12345, 812345
        * 7-JM183
        */
        $looksLikePension = (bool) preg_match(
            '/^(7-[A-Z0-9]{5}|8-?\d{5})$/i',
            $compact
        );

        /*
        * 5. NIF
        *
        * Exemples :
        * 123-456-789-0
        * 1234567890
        */
        $looksLikeNif = (bool) preg_match(
            '/^\d{3}-?\d{3}-?\d{3}-?\d$/',
            $compact
        ) || strlen($digits) === 10;

        if ($looksLikePension) {
            return self::findByPensionCode($identifier);
        }

        if ($looksLikeNif) {
            return self::findByNif($identifier);
        }

        /*
        * 6. Format non reconnu :
        * essayer pension puis NIF.
        */
        return self::findByPensionCode($identifier)
            ?? self::findByNif($identifier);
    }

    public static function findByNif(string $nif): ?self
    {
        $digits = self::normalizeDigits($nif);

        if ($digits === '') {
            return null;
        }

        return static::query()
            ->whereRaw(self::digitsSql('nif').' = ?', [$digits])
            ->first();
    }

    public static function findByPensionCode(string $code): ?self
    {
        $affiliation = AffiliationPension::findByCode($code);

        if ($affiliation) {
            $beneficiaire = $affiliation->beneficiaire;

            if ($beneficiaire) {
                $selfLink = UserBeneficiaireLink::query()
                    ->where('beneficiaire_id', $beneficiaire->id)
                    ->where('relation', \App\Enums\BeneficiaireRelationEnum::SELF->value)
                    ->first();

                $linkUser = $selfLink?->user ?? $beneficiaire->users()->first();

                if ($linkUser) {
                    return $linkUser;
                }
            }
        }

        return null;
    }

    public static function findByNinu(string $ninu): ?self
    {
        $digits = self::normalizeDigits($ninu);

        if ($digits === '') {
            return null;
        }

        return static::query()
            ->whereRaw(self::digitsSql('ninu').' = ?', [$digits])
            ->first();
    }

    public static function findByTelephone(string $phone): ?self
    {
        $phone = preg_replace('/\s+/', '', trim($phone)) ?? '';

        if ($phone === '' || ! str_starts_with($phone, '+')) {
            return null;
        }

        $digits = self::normalizeDigits($phone);

        if ($digits === '') {
            return null;
        }

        return static::query()
            ->whereRaw(self::digitsSql('phone').' = ?', [$digits])
            ->first();
    }

    public static function nifExists(string $nif): bool
    {
        return self::findByNif($nif) !== null;
    }

    public static function ninuExists(string $ninu): bool
    {
        return self::findByNinu($ninu) !== null;
    }

    public static function pensionCodeExists(string $code): bool
    {
        return AffiliationPension::codeExists($code);
    }

    public static function telephoneExists(string $phone): bool
    {
        return self::findByTelephone($phone) !== null;
    }

    public function routeNotificationForTwilio(): ?string
    {
        $phone = preg_replace('/\s+/', '', trim((string) ($this->phone ?? ''))) ?? '';

        if ($phone === '') {
            return null;
        }

        return str_starts_with($phone, '+') ? $phone : null;
    }

    public static function digitsComparisonSql(string $column): string
    {
        return self::digitsSql($column);
    }

    private static function digitsSql(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(COALESCE({$column}, ''), '-', ''), ' ', ''), '.', ''), '+', '')";
    }

    public static function usernameExists(string $username): bool
    {
        return static::query()
            ->whereRaw('LOWER(username) = ?', [self::normalizeUsername($username)])
            ->exists();
    }

    public static function emailExists(string $email): bool
    {
        return static::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($email))])
            ->exists();
    }

    /** Historique des demandes de création de compte liées à cet utilisateur. */
    public function demandesCreationCompte(): HasMany
    {
        return $this->hasMany(DemandeCreationCompte::class);
    }

    /** Dernière demande de création de compte (lien provisoire → permanent via {@see DemandeCreationCompte::$user_id}). */
    public function demandeCreationCompte(): HasOne
    {
        return $this->hasOne(DemandeCreationCompte::class)->latestOfMany();
    }

    public function categoryAssignments(): HasMany
    {
        return $this->hasMany(UserCategoryAssignment::class);
    }

    public function institution(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(UserCategory::class, 'user_category_assignments', 'user_id', 'user_category_code')
            ->withPivot(['is_primary', 'valid_from', 'valid_until'])
            ->withTimestamps();
    }

    public function categoryResolver(): UserCategoryResolver
    {
        return UserCategoryResolver::for($this);
    }

    public function hasCategory(string $code): bool
    {
        return $this->categoryResolver()->hasCategory($code);
    }

    public function primaryCategoryCode(): ?string
    {
        return $this->categoryResolver()->primaryCategoryCode();
    }

    public function hasPensionPortalAccess(): bool
    {
        return $this->categoryResolver()->hasPensionPortalAccess();
    }

    public function hasAgentPortalAccess(): bool
    {
        return $this->categoryResolver()->hasAgentPortalAccess();
    }

    public function beneficiaireLinks(): HasMany
    {
        return $this->hasMany(UserBeneficiaireLink::class);
    }

    public function beneficiaires(): BelongsToMany
    {
        return $this->belongsToMany(Beneficiaire::class, 'user_beneficiaire_links')
            ->withPivot(['relation', 'valid_from', 'valid_until'])
            ->withTimestamps();
    }

    public function pensionMandats(): HasMany
    {
        return $this->hasMany(PensionMandat::class);
    }

    public function pensionContext(): ActivePensionContext
    {
        return ActivePensionContext::for($this);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function isDirection(): bool
    {
        return $this->hasAnyRole(self::DIRECTION_ROLES);
    }

    public function resolvedAccountStatus(): AccountStatusEnum
    {
        return AccountStatusEnum::tryFromStored($this->account_status)
            ?? AccountStatusEnum::default();
    }

    public function isProvisionnel(): bool
    {
        return $this->resolvedAccountStatus()->isProvisionnel();
    }

    public function canAccessFullServices(): bool
    {
        return $this->resolvedAccountStatus()->isActif();
    }

    /** Identifiants pension (NIF, code) visibles uniquement pour un compte actif. */
    public function exposesPensionIdentity(): bool
    {
        return $this->canAccessFullServices();
    }

    public function displayNif(): ?string
    {
        return $this->exposesPensionIdentity() ? $this->nif : null;
    }

    public function displayPensionCode(): ?string
    {
        if (! $this->exposesPensionIdentity()) {
            return null;
        }

        return $this->pensionContext()->displayPensionCode();
    }

    public function allowsLogin(): bool
    {
        return $this->resolvedAccountStatus()->allowsLogin();
    }

    public function activateAccount(): void
    {
        $this->update(['account_status' => AccountStatusEnum::ACTIF]);
    }

    public function toggleAdministrativeAccess(): void
    {
        $status = $this->resolvedAccountStatus();

        $this->update([
            'account_status' => $status->allowsLogin()
                ? AccountStatusEnum::DESACTIVE
                : AccountStatusEnum::ACTIF,
        ]);
    }

    public function accountStatusLabel(): string
    {
        return $this->resolvedAccountStatus()->label();
    }

    public function scopeAccountOperational($query)
    {
        return $query->where(
            'account_status',
            AccountStatusEnum::ACTIF->value
        );
    }

    public function displayName(): string
    {
        if (filled($this->name)) {
            return (string) $this->name;
        }

        return trim(implode(' ', array_filter([$this->firstname, $this->lastname])));
    }

    public function formPrenom(): string
    {
        if (filled($this->firstname)) {
            return (string) $this->firstname;
        }

        $parts = preg_split('/\s+/u', trim((string) $this->name), 2) ?: [];

        return (string) ($parts[0] ?? '');
    }

    public function formNom(): string
    {
        if (filled($this->lastname)) {
            return (string) $this->lastname;
        }

        $parts = preg_split('/\s+/u', trim((string) $this->name), 2) ?: [];

        return (string) ($parts[1] ?? '');
    }
}