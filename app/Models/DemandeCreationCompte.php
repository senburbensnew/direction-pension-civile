<?php

namespace App\Models;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DemandeCreationCompte extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const STATUS_EN_ATTENTE = 'en_attente';

    public const STATUS_ACCEPTEE = 'acceptee';

    public const STATUS_REFUSEE = 'refusee';

    public const MEDIA_LABELS = [
        'identite_permis' => 'Permis de conduire',
        'identite_passeport' => 'Passeport',
        'identite_cin' => 'Carte d’identification nationale',
        'acte_naissance' => 'Acte de naissance',
        'identite_representant' => 'Pièce d’identité du représentant',
        'identite_representant_permis' => 'Permis du représentant',
        'identite_representant_passeport' => 'Passeport du représentant',
        'identite_representant_cin' => 'CIN du représentant',
    ];

    protected $fillable = [
        'code',
        'idempotency_key', 
        'user_type',
        'firstname',
        'lastname',
        'name',
        'email',
        'username',
        'telephone',
        'adresse',
        'nif',
        'ninu',
        'pension_code',
        'is_mineur',
        'representant_lien',
        'piece_identite_representant_type',
        'pieces_identite',
        'ocr_fields',
        'ocr_documents',
        'galipec_snapshot',
        'verification_mismatches',
        'submitted_payload',
        'message',
        'refusal_reason',
        'accepted_terms_at',
        'status',
        'reviewed_at',
        'reviewed_by',
        'user_id',
    ];

    protected $casts = [
        'is_mineur' => 'boolean',
        'pieces_identite' => 'array',
        'ocr_fields' => 'array',
        'ocr_documents' => 'array',
        'galipec_snapshot' => 'array',
        'verification_mismatches' => 'array',
        'submitted_payload' => 'array',
        'accepted_terms_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('identite_permis')->singleFile();
        $this->addMediaCollection('identite_passeport')->singleFile();
        $this->addMediaCollection('identite_cin')->singleFile();
        $this->addMediaCollection('acte_naissance')->singleFile();
        $this->addMediaCollection('identite_representant')->singleFile();
        $this->addMediaCollection('identite_representant_permis')->singleFile();
        $this->addMediaCollection('identite_representant_passeport')->singleFile();
        $this->addMediaCollection('identite_representant_cin')->singleFile();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(DemandeCreationCompteHistory::class)->latest();
    }

    /**
     * @param  array<string, mixed>|null  $champs
     */
    public function recordHistory(string $event, string $commentaire, ?User $actor = null, ?string $statut = null, ?array $champs = null): DemandeCreationCompteHistory
    {
        $history = $this->histories()->create([
            'event' => $event,
            'statut' => $statut ?? $this->status,
            'commentaire' => $commentaire,
            'changed_by' => $actor?->id,
            'champs' => $champs,
        ]);

        activity('compte_creation')
            ->causedBy($actor)
            ->performedOn($this)
            ->withProperties([
                'event' => $event,
                'statut' => $statut ?? $this->status,
                'champs' => $champs,
            ])
            ->log($commentaire);

        return $history;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_EN_ATTENTE;
    }

    public function hasRendezVousRealise(): bool
    {
        if (! $this->user_id) {
            return false;
        }

        return Demande::query()
            ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->where('created_by', $this->user_id)
            ->where('data->rdv_statut', RencontreStatutEnum::REALISE->value)
            ->exists();
    }

    public function canBeDecided(): bool
    {
        return $this->isPending() && $this->hasRendezVousRealise();
    }

    public function displayName(): string
    {
        $full = trim(implode(' ', array_filter([$this->firstname, $this->lastname])));

        return $full !== '' ? $full : ($this->name ?: $this->nif ?: 'Demandeur');
    }

    /**
     * Vérifie qu'une valeur en attente n'est pas déjà utilisée par une
     * autre demande dont le statut est « en_attente ».
     *
     * On compare sur les chiffres uniquement, pour ignorer les tirets,
     * espaces, points et indicatifs éventuels.
     */
    public static function pendingDigitsMatch(string $column, string $digits): bool
    {
        $allowed = ['nif', 'ninu', 'pension_code', 'telephone'];

        if ($digits === '' || ! in_array($column, $allowed, true)) {
            return false;
        }

        return static::query()
            ->where('status', self::STATUS_EN_ATTENTE)
            ->whereRaw(
                "REPLACE(REPLACE(REPLACE(COALESCE({$column}, ''), '-', ''), ' ', ''), '.', '') = ?",
                [$digits]
            )
            ->exists();
    }

    public static function pendingTelephoneExists(string $telephone): bool
    {
        $digits = User::normalizeDigits($telephone);

        if ($digits === '') {
            return false;
        }

        return static::pendingDigitsMatch('telephone', $digits);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACCEPTEE => 'Acceptée',
            self::STATUS_REFUSEE => 'Refusée',
            default => 'En attente',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_ACCEPTEE => 'bg-green-100 text-green-800',
            self::STATUS_REFUSEE => 'bg-red-100 text-red-800',
            default => 'bg-amber-100 text-amber-800',
        };
    }

    public function purgeIdentityDocuments(): void
    {
        foreach (array_keys(self::MEDIA_LABELS) as $collection) {
            $this->clearMediaCollection($collection);
        }
    }

    public function purgePersonalData(): void
    {
        $this->purgeIdentityDocuments();

        $this->forceFill([
            'firstname' => null,
            'lastname' => null,
            'name' => null,
            'email' => null,
            'username' => null,
            'telephone' => null,
            'adresse' => null,
            'nif' => null,
            'ninu' => null,
            'pension_code' => null,
            'representant_lien' => null,
            'piece_identite_representant_type' => null,
            'pieces_identite' => null,
            'ocr_fields' => null,
            'ocr_documents' => null,
            'galipec_snapshot' => null,
            'verification_mismatches' => null,
            'submitted_payload' => null,
            'message' => null,
            'user_id' => null,
        ])->save();
    }

    /**
     * @return list<array{collection: string, label: string, media: Media}>
     */
    public function identityPieces(): array
    {
        $pieces = [];

        foreach (self::MEDIA_LABELS as $collection => $label) {
            $media = $this->getFirstMedia($collection);
            if ($media) {
                $pieces[] = [
                    'collection' => $collection,
                    'label' => $label,
                    'media' => $media,
                ];
            }
        }

        return $pieces;
    }
}