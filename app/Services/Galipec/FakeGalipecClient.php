<?php

namespace App\Services\Galipec;

class FakeGalipecClient implements GalipecClient
{
    /** @var list<GalipecPensionne> */
    public static array $records = [];

    public static function reset(): void
    {
        self::$records = [];
    }

    public function findByTelephone(string $telephone): ?GalipecPensionne
    {
        if (self::$records === []) {
            return new GalipecPensionne(found: true, telephone: $telephone);
        }

        foreach (self::$records as $record) {
            if ($this->samePhone($record->telephone, $telephone)) {
                return $record;
            }
        }

        return null;
    }

    public function findByNif(string $nif): ?GalipecPensionne
    {
        $normalized = $this->normalizeNif($nif);

        foreach (self::$records as $record) {
            if ($record->nif && $this->normalizeNif($record->nif) === $normalized) {
                return $record;
            }
        }

        return null;
    }

    private function samePhone(?string $left, ?string $right): bool
    {
        return $this->digits($left) !== '' && $this->digits($left) === $this->digits($right);
    }

    private function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    private function normalizeNif(string $nif): string
    {
        return preg_replace('/\D+/', '', $nif) ?? '';
    }
}
