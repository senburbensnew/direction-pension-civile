<?php

namespace App\Models;

use App\Enums\TypeDemandeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;

class FluxTransition extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_source_id',
        'service_destination_id',
        'action',
        'type_demande',
        'ordre',
    ];

    public function sourceService()
    {
        return $this->belongsTo(Service::class, 'service_source_id');
    }

    public function destinationService()
    {
        return $this->belongsTo(Service::class, 'service_destination_id');
    }

    /**
     * Returns the Service models reachable from a given service,
     * optionally filtered by demande type.
     */
    public static function destinationsFor(int $serviceId, ?string $typeDemandeCode = null): Collection
    {
        $query = static::where('service_source_id', $serviceId)
            ->where(function ($q) use ($typeDemandeCode) {
                $q->whereNull('type_demande');
                if ($typeDemandeCode) {
                    $q->orWhere('type_demande', $typeDemandeCode);
                }
            })
            ->orderBy('ordre');

        $ids = $query->pluck('service_destination_id');

        return Service::whereIn('id', $ids)->get();
    }

    /**
     * Check whether a transition is allowed, optionally for a specific demande type.
     */
    public static function allowed(int $fromServiceId, int $toServiceId, ?string $typeDemandeCode = null): bool
    {
        return static::where('service_source_id', $fromServiceId)
            ->where('service_destination_id', $toServiceId)
            ->where(function ($q) use ($typeDemandeCode) {
                $q->whereNull('type_demande');
                if ($typeDemandeCode) {
                    $q->orWhere('type_demande', $typeDemandeCode);
                }
            })
            ->exists();
    }

    public function typeDemandeLabelAttribute(): ?string
    {
        if (!$this->type_demande) return null;
        $enum = TypeDemandeEnum::tryFrom($this->type_demande);
        return $enum?->label();
    }
}
