<?php

namespace App\Services\Galipec;

use Illuminate\Support\Facades\Http;

class HttpGalipecClient implements GalipecClient
{
    public function findByTelephone(string $telephone): ?GalipecPensionne
    {
        return $this->lookup(['telephone' => $telephone]);
    }

    public function findByNif(string $nif): ?GalipecPensionne
    {
        return $this->lookup(['nif' => $nif]);
    }

    /**
     * @param  array<string, string>  $query
     */
    private function lookup(array $query): ?GalipecPensionne
    {
        if (! config('galipec.enabled') || ! config('galipec.base_url')) {
            return null;
        }

        $request = Http::timeout((int) config('galipec.timeout', 15))
            ->acceptJson();

        if ($token = config('galipec.token')) {
            $request = $request->withToken($token);
        }

        $response = $request->get(rtrim((string) config('galipec.base_url'), '/').'/pensionnes', $query);

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json('data') ?? $response->json();

        if (! is_array($payload) || ($payload['found'] ?? true) === false) {
            return null;
        }

        $record = GalipecPensionne::fromArray($payload);

        return $record->found ? $record : null;
    }
}
