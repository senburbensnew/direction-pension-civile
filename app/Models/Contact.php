<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public const MESSAGE_MAX_LENGTH = 1000;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'telephone',
        'subject',
        'destinataire',
        'message',
        'read',
    ];

    public static function destinataireKey(string $type, string $id): string
    {
        return $type . ':' . $id;
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
                ? $direction->nom . ' (' . $direction->abbr . ')'
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
}
