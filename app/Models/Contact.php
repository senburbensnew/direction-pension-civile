<?php

namespace App\Models;

use App\Models\Concerns\IncludesTrashedInAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use IncludesTrashedInAdmin;
    use SoftDeletes;

    public const MESSAGE_MAX_LENGTH = 1000;

    public const STATUS_NOUVEAU = 'nouveau';

    public const STATUS_EN_COURS = 'en_cours';

    public const STATUS_TRAITE = 'traite';

    public const STATUSES = [
        self::STATUS_NOUVEAU,
        self::STATUS_EN_COURS,
        self::STATUS_TRAITE,
    ];

    private const STATUS_LABELS = [
        self::STATUS_NOUVEAU => 'Nouveau',
        self::STATUS_EN_COURS => 'En cours',
        self::STATUS_TRAITE => 'Traité',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'telephone',
        'subject',
        'destinataire',
        'message',
        'status',
        'read',
    ];

    protected $casts = [
        'read' => 'boolean',
    ];

    public static function destinataireKey(string $type, string $id): string
    {
        return $type.':'.$id;
    }

    public function destinataireLabel(): string
    {
        if (! $this->destinataire || ! str_contains($this->destinataire, ':')) {
            return $this->destinataire ?: '—';
        }

        [$type, $id] = explode(':', $this->destinataire, 2);

        if ($type === 'service') {
            return Service::query()->where('code', $id)->value('nom') ?: $id;
        }

        if ($type === 'direction') {
            $direction = DirectionDepartementale::query()->where('abbr', $id)->first();

            return $direction
                ? $direction->nom.' ('.$direction->abbr.')'
                : $id;
        }

        return $this->destinataire;
    }

    public function subjectLabel(): string
    {
        $label = ContactSubject::query()
            ->where('slug', $this->subject)
            ->value('label');

        return $label ?: $this->subject;
    }

    public function isCustomSubject(): bool
    {
        return ! ContactSubject::query()->where('slug', $this->subject)->exists();
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? (string) ($this->status ?? '—');
    }

    public function transitionTo(string $status): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            return;
        }

        $this->status = $status;

        if ($status === self::STATUS_TRAITE) {
            $this->read = true;
        }

        $this->save();
    }
}
