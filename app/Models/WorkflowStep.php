<?php

namespace App\Models;

use App\Enums\WorkflowStepTypeEnum;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    protected $fillable = [
        'code',
        'nom',
        'description',
        'service_id',
        'type_demande',
        'ordre',
        'type_noeud',
    ];

    protected $casts = [
        'type_noeud' => WorkflowStepTypeEnum::class,
    ];

    public function isInitial(): bool
    {
        return $this->type_noeud === WorkflowStepTypeEnum::INITIAL;
    }

    public function isIntermediaire(): bool
    {
        return $this->type_noeud === WorkflowStepTypeEnum::INTERMEDIAIRE;
    }

    public function isTerminal(): bool
    {
        return $this->type_noeud === WorkflowStepTypeEnum::TERMINAL;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function demandes()
    {
        return $this->hasMany(Demande::class, 'current_step_id');
    }

    // ── Static lookups ──────────────────────────────────────────────────

    /**
     * Find the step for a service + demande type.
     * Looks for a type-specific step first, falls back to the global (type_demande = null) step.
     */
    public static function forService(int $serviceId, ?string $type = null): ?self
    {
        if ($type) {
            $specific = self::where('service_id', $serviceId)
                ->where('type_demande', $type)
                ->first();

            if ($specific) {
                return $specific;
            }
        }

        return self::where('service_id', $serviceId)
            ->whereNull('type_demande')
            ->first();
    }

    /**
     * Find a step by its code.
     * Tries type-specific first, falls back to global.
     */
    public static function forCode(string $code, ?string $type = null): ?self
    {
        if ($type) {
            $specific = self::where('code', $code)->where('type_demande', $type)->first();
            if ($specific) return $specific;
        }

        return self::where('code', $code)->whereNull('type_demande')->first();
    }

    /** Return the ID of the global step matching $code, or null. */
    public static function idForCode(string $code, ?string $type = null): ?int
    {
        return self::forCode($code, $type)?->id;
    }

    // ── Display helpers (ported from Etat) ──────────────────────────────

    public static function getStatusStyle(string $code): string
    {
        return match($code) {
            'EN_ATTENTE'            => 'bg-yellow-100 text-yellow-800',
            'APPROUVEE'             => 'bg-blue-100 text-blue-800',
            'EN_COURS'              => 'bg-purple-100 text-purple-800',
            'REJETEE'               => 'bg-red-100 text-red-800',
            'FINALISEE'             => 'bg-green-100 text-green-800',
            'ANNULEE'               => 'bg-red-100 text-red-800',
            'COMPLEMENT_REQUIS'     => 'bg-orange-100 text-orange-800',
            'TRANSFERT_EN_ATTENTE'  => 'bg-sky-100 text-sky-800',
            'TRANSFERT_REFUSE'      => 'bg-red-100 text-red-800',
            default                 => 'bg-gray-100 text-gray-800',
        };
    }

    public function styleClass(): string
    {
        return self::getStatusStyle($this->code);
    }
}
