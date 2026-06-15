<?php

namespace App\Models;

use App\Enums\TypeDemandeEnum;
use Illuminate\Database\Eloquent\Model;

class StepRequiredDocument extends Model
{
    protected $fillable = ['workflow_step_id', 'type_demande', 'label', 'document_type'];

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    public function typeDemandeLabelAttribute(): ?string
    {
        if (!$this->type_demande) return null;
        return TypeDemandeEnum::tryFrom($this->type_demande)?->label();
    }

    /**
     * Returns document requirements for a given workflow step and demande type.
     * NULL type_demande means the requirement applies to all types.
     */
    public static function forStep(int $stepId, ?string $typeDemandeCode = null): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('workflow_step_id', $stepId)
            ->where(function ($q) use ($typeDemandeCode) {
                $q->whereNull('type_demande');
                if ($typeDemandeCode) {
                    $q->orWhere('type_demande', $typeDemandeCode);
                }
            })
            ->get();
    }
}
