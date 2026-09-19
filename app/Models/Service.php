<?php

namespace App\Models;

use App\Models\Demande;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    // Codes des services
    public const DIRECTION            = 'direction';
    public const SECRETARIAT          = 'secretariat';
    public const LIQUIDATION          = 'service_liquidation';
    public const CONTROLE_PLACEMENT   = 'service_controle_placement';
    public const COMPTABILITE         = 'service_comptabilite';
    public const FORMALITE            = 'service_accueil_formalites';
    public const ASSURANCE            = 'service_assurance';
    public const ARCHIVES             = 'service_archives';
    public const ADMINISTRATIF        = 'cellule_administration';

    protected $fillable = [
        'code',
        'nom',
        'description',
        'icon',
        'color',
    ];

    /**
     * @return list<string>
     */
    public static function publicCodes(): array
    {
        return array_column(config('dpc_services'), 'code');
    }

    /**
     * @return array<string, list<string>>
     */
    public static function publicCodeAliases(): array
    {
        return [
            self::FORMALITE => ['service_formalite'],
        ];
    }

    public static function publicOrdered()
    {
        $catalog = config('dpc_services');
        $aliases = self::publicCodeAliases();
        $lookupCodes = self::publicCodes();

        foreach ($aliases as $legacyCodes) {
            $lookupCodes = array_merge($lookupCodes, $legacyCodes);
        }

        $byCode = self::query()
            ->whereIn('code', array_unique($lookupCodes))
            ->get()
            ->keyBy('code');

        return collect($catalog)
            ->map(function (array $item) use ($byCode, $aliases) {
                $service = $byCode->get($item['code']);

                if (! $service) {
                    foreach ($aliases[$item['code']] ?? [] as $legacyCode) {
                        $service = $byCode->get($legacyCode);
                        if ($service) {
                            break;
                        }
                    }
                }

                if (! $service) {
                    $service = new self([
                        'code' => $item['code'],
                        'nom'  => $item['nom'],
                        'icon' => $item['icon'] ?? null,
                    ]);
                    $service->code = $item['code'];
                }

                $service->nom = $item['nom'] ?? $service->nom;

                return $service;
            })
            ->values();
    }

    public function catalog(): ?array
    {
        return collect(config('dpc_services'))->firstWhere('code', $this->code);
    }

    public function applyCatalogNom(): self
    {
        if ($nom = $this->catalog()['nom'] ?? null) {
            $this->nom = $nom;
        }

        return $this;
    }

    /**
     * @return list<string>
     */
    public function attributions(): array
    {
        return $this->catalog()['attributions'] ?? [];
    }

    public function demandes()
    {
        return $this->hasMany(Demande::class, 'current_service_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'service_id');
    }
}
