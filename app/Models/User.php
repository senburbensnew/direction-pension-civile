<?php

namespace App\Models;

use App\Models\Gender;
use App\Models\Service;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public const STATUS_ACTIF = 'actif';

    public const STATUS_EN_ATTENTE_VALIDATION = 'en_attente_validation';

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
        'user_type_id',
        'service_id',
        'pension_code',
        'is_active',
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
        'is_active' => 'boolean',
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

    public static function uniqueUsernameFrom(?string $source, ?int $ignoreId = null): string
    {
        $base = strtolower((string) $source);
        if (str_contains($base, '@')) {
            $base = Str::before($base, '@');
        }

        $base = preg_replace('/[^a-z0-9._-]/', '', $base) ?: 'user';
        $base = substr($base, 0, 40);
        $candidate = $base;
        $suffix = 0;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->whereRaw('LOWER(username) = ?', [$candidate])
            ->exists()) {
            $suffix++;
            $candidate = $base.$suffix;
        }

        return $candidate;
    }

    public static function normalizeDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    public static function findForLogin(string $identifier): ?self
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        if (str_contains($identifier, '@')) {
            return static::query()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($identifier)])
                ->first();
        }

        $compact = strtoupper(preg_replace('/\s+/', '', $identifier) ?? '');
        $digits = self::normalizeDigits($identifier);
        $looksLikePension = (bool) preg_match('/^\d-?\d{5}$/', $compact);
        $looksLikeNif = (bool) preg_match('/^\d{3}-?\d{3}-?\d{3}-?\d$/', $compact) || strlen($digits) === 10;

        if ($looksLikePension) {
            return self::findByPensionCode($identifier);
        }

        if ($looksLikeNif) {
            return self::findByNif($identifier);
        }

        return self::findByPensionCode($identifier) ?? self::findByNif($identifier);
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
        $compact = strtoupper(preg_replace('/\s+/', '', trim($code)) ?? '');
        $digits = self::normalizeDigits($code);

        if ($compact === '') {
            return null;
        }

        return static::query()
            ->where(function ($query) use ($compact, $digits) {
                $query->whereRaw("UPPER(REPLACE(COALESCE(pension_code, ''), ' ', '')) = ?", [$compact]);
                if ($digits !== '') {
                    $query->orWhereRaw(self::digitsSql('pension_code').' = ?', [$digits]);
                }
            })
            ->first();
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
        return self::findByPensionCode($code) !== null;
    }

    public static function telephoneExists(string $phone): bool
    {
        return self::findByTelephone($phone) !== null;
    }

    private static function digitsSql(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(COALESCE({$column}, ''), '-', ''), ' ', ''), '.', '')";
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

    public function userType()
    {
        return $this->belongsTo(UserType::class);
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

    public function isProvisionnel(): bool
    {
        return ($this->account_status ?: self::STATUS_ACTIF) === self::STATUS_EN_ATTENTE_VALIDATION;
    }

    public function canAccessFullServices(): bool
    {
        return $this->is_active && ! $this->isProvisionnel();
    }

    public function activateAccount(): void
    {
        $this->update(['account_status' => self::STATUS_ACTIF]);
    }

    public function accountStatusLabel(): string
    {
        return $this->isProvisionnel() ? 'En attente de validation' : 'Actif';
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