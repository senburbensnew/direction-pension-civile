<?php

namespace App\Models;

use App\Enums\TypeDemandeEnum;
use Illuminate\Database\Eloquent\Model;

class StepRequiredDocument extends Model
{
    protected $fillable = ['service_id', 'type_demande', 'label', 'document_type'];

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
     * Returns document requirements for a given service and demande type.
     * NULL type_demande means the requirement applies to all types.
     */
    public static function forService(int $serviceId, ?string $typeDemandeCode = null): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('service_id', $serviceId)
            ->where(function ($q) use ($typeDemandeCode) {
                $q->whereNull('type_demande');
                if ($typeDemandeCode) {
                    $q->orWhere('type_demande', $typeDemandeCode);
                }
            })
            ->get();
    }
}
