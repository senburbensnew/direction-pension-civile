<?php

namespace App\Services\Galipec;

interface GalipecClient
{
    public function findByTelephone(string $telephone): ?GalipecPensionne;

    public function findByNif(string $nif): ?GalipecPensionne;
}
