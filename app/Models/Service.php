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
    public const FORMALITE            = 'service_formalite';
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

    public static function publicOrdered()
    {
        $order = array_flip(self::publicCodes());

        return self::query()
            ->whereIn('code', self::publicCodes())
            ->get()
            ->sortBy(fn (self $service) => $order[$service->code] ?? 99)
            ->values();
    }

    public function catalog(): ?array
    {
        return collect(config('dpc_services'))->firstWhere('code', $this->code);
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
