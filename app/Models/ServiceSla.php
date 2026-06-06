<?php

namespace App\Models;

use App\Enums\TypeDemandeEnum;
use Illuminate\Database\Eloquent\Model;

class ServiceSla extends Model
{
    protected $table = 'service_sla';

    protected $fillable = ['service_id', 'type_demande', 'delai_jours'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function typeDemandeLabelAttribute(): ?string
    {
        if (!$this->type_demande) return null;
        return TypeDemandeEnum::tryFrom($this->type_demande)?->label();
    }

    /**
     * Returns the SLA in days for a given service and demande type.
     * Falls back to the generic SLA (type_demande IS NULL) if no specific one is set.
     */
    public static function forService(int $serviceId, ?string $typeDemandeCode = null): ?self
    {
        if ($typeDemandeCode) {
            $specific = static::where('service_id', $serviceId)
                ->where('type_demande', $typeDemandeCode)
                ->first();
            if ($specific) return $specific;
        }

        return static::where('service_id', $serviceId)
            ->whereNull('type_demande')
            ->first();
    }
}
