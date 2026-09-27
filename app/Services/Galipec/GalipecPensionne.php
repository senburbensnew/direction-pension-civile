<?php

namespace App\Services\Galipec;

class GalipecPensionne
{
    public function __construct(
        public readonly bool $found = false,
        public readonly ?string $nif = null,
        public readonly ?string $codePension = null,
        public readonly ?string $nom = null,
        public readonly ?string $prenom = null,
        public readonly ?string $telephone = null,
        public readonly ?string $adresse = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            found: (bool) ($data['found'] ?? true),
            nif: $data['nif'] ?? $data['NIF'] ?? null,
            codePension: $data['code_pension'] ?? $data['pension_code'] ?? null,
            nom: $data['nom'] ?? $data['lastname'] ?? null,
            prenom: $data['prenom'] ?? $data['firstname'] ?? null,
            telephone: $data['telephone'] ?? $data['phone'] ?? null,
            adresse: $data['adresse'] ?? $data['address'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'found' => $this->found,
            'nif' => $this->nif,
            'code_pension' => $this->codePension,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
        ];
    }
}
