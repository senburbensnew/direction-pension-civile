<?php

namespace App\Models;

use App\Models\CivilStatus;
use App\Models\DemandeWorkflow;
use App\Models\Gender;
use App\Models\PensionCategory;
use App\Models\PensionType;
use App\Models\Service;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Demande extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'type',
        'created_by',
        'status_id',
        'data',
        'current_service_id',
        'submitted_at',
        'expires_at',
        'annotation',
        'annotated_by',
        'annotated_at',
        'folder',
        'categorie',
        'is_urgent',
    ];

    protected $attributes = [
        'data' => '[]',
    ];

    protected $casts = [
        'data'         => 'array',
        'submitted_at' => 'datetime',
        'expires_at'   => 'datetime',
        'annotated_at' => 'datetime',
        'is_urgent'    => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($demande) {
            if (is_null($demande->status_id)) {
                $demande->status_id = Status::where('code', 'BROUILLON')->value('id');
            }
            if (empty($demande->title) && $demande->type) {
                $demande->title = \App\Enums\TypeDemandeEnum::from($demande->type)->label();
            }
            if (empty($demande->code) && $demande->type) {
                $prefix = $demande->type . '-' . now()->format('Ymd') . '-';
                do {
                    $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
                    $code   = $prefix . $random;
                } while (static::where('code', $code)->exists());
                $demande->code = $code;
            }
            // Auto-classify: urgence prime sur le type
            if ($demande->type) {
                $typeCat = \App\Enums\TypeDemandeEnum::from($demande->type)->categorie()->value;
                $demande->categorie = $demande->is_urgent
                    ? \App\Enums\CategorieDossierEnum::DOSSIERS_URGENTS->value
                    : $typeCat;
            }
        });
    }

    /* ================= Relations ================= */
    
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'current_service_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DemandeDocument::class);
    }

    public function workflows()
    {
        return $this->hasMany(DemandeWorkflow::class);
    }

    public function histories()
    {
        return $this->hasMany(DemandeHistory::class);
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function annotatedBy()
    {
        return $this->belongsTo(User::class, 'annotated_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(DemandeActivityLog::class);
    }

    public function messages()
    {
        return $this->hasMany(DemandeMessage::class)->orderBy('created_at', 'asc');
    }

     /* ================= Helpers ================= */
     public function addWorkflow($toServiceId, $statusId, $commentaire = null)
    {
        return $this->workflows()->create([
            'from_service_id'   => $this->getOriginal('current_service_id'),
            'to_service_id'     => $toServiceId,
            'status_id'         => $statusId,
            'action_by_user_id' => auth()->id(),
            'commentaire'       => $commentaire,
        ]);
    }

    public function isAnnotated(): bool
    {
        return !is_null($this->annotated_at);
    }

    public function isDraft()
    {
        return $this->status->code === 'BROUILLON';
    }

    public function isSubmitted()
    {
        return $this->status->code === 'SOUMISE';
    }

    public function needsComplement(): bool
    {
        return $this->status->code === 'COMPLEMENT_REQUIS';
    }

    public function canBeEditedByUser(): bool
    {
        return in_array($this->status->code, ['BROUILLON', 'COMPLEMENT_REQUIS']);
    }

    public function isExpired()
    {
        return $this->status->code === 'BROUILLON' && $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Urgent if manually flagged OR pending for more than 30 days.
     */
    public function isUrgent(): bool
    {
        if ($this->is_urgent) {
            return true;
        }
        return $this->submitted_at && $this->submitted_at->diffInDays(now()) > 30;
    }

    public function categorieEnum(): ?\App\Enums\CategorieDossierEnum
    {
        return $this->categorie
            ? \App\Enums\CategorieDossierEnum::tryFrom($this->categorie)
            : null;
    }

    public function categorieLabel(): string
    {
        return $this->categorieEnum()?->label() ?? '—';
    }

    public function documentsByType(string $type)
    {
        return $this->documents()->where('type', $type);
    }

    public function hasDocument(string $type): bool
    {
        return $this->documents()->where('type', $type)->exists();
    }

    public function civilStatus($name='civil_status_id')
    {
        if (!isset($this->data[$name])) {
            return null;
        }

        return CivilStatus::find($this->data[$name]);
    }

    public function gender($sexeId)
    {
        return Gender::find($sexeId);
    }

    public function pensionType($name='pension_type_id')
    {
        if (!isset($this->data[$name])) {
            return null;
        }

        return PensionType::find($this->data[$name]);
    }

    public function pensionCategory($name='pension_category_id')
    {
        if (!isset($this->data[$name])) {
            return null;
        }

        return PensionCategory::find($this->data[$name]);
    }

    /* 
        public function parseDate($date)
        {
            return $date ? Carbon::parse($date) : null;
        } 
    */

    public function scopeForUser($query)
    {
        return $query->where('created_by', auth()->id());
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePending($query)
    {
        return $query->where('status_id', Status::getStatusPending()->id);
    }

    public function scopeApproved($query)
    {
        return $query->where('status_id', Status::getStatusApproved()->id);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status_id', Status::getStatusInProgress()->id);
    }

    public function scopeRejected($query)
    {
        return $query->where('status_id', Status::getStatusRejected()->id);
    }

    public function scopeCanceled($query)
    {
        return $query->where('status_id', Status::getStatusCanceled()->id);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status_id', Status::getStatusCompleted()->id);
    }
}
