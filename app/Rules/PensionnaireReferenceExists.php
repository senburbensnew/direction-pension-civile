<?php

namespace App\Rules;

use App\Models\PensionnaireReference;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;

class PensionnaireReferenceExists implements ValidationRule, DataAwareRule
{
    /**
     * Payload complet injecté par Laravel (grâce à DataAwareRule).
     */
    protected array $data = [];

    /**
     * Si true, un code existant avec un nom/prénom discordant déclenche
     * une erreur. Si false, on tolère la discordance (simple avertissement
     * non bloquant — cf. méthode `warning()`).
     */
    protected bool $strictNameMatch = true;

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /* ============================================================
     * VALIDATION
     * ============================================================ */

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return; // contrôle uniquement si un code est fourni
        }

        $code = $this->normalizeCode((string) $value);
        [$nom, $prenom] = $this->resolveNomPrenom();

        // ── 1) Recherche par code (insensible à la casse et aux espaces)
        $candidates = $this->findByCode($code);

        if ($candidates->isEmpty()) {
            $fail("Le code pension « {$code} » n'existe pas dans la base de référence.");
            return;
        }

        // ── 2) Si aucun nom/prénom exploitable → code seul suffit
        $expectedTokens = $this->tokenize($nom, $prenom);

        if ($expectedTokens === []) {
            return;
        }

        // ── 3) Comparer les tokens (nom + prénom) côté PHP
        $matched = $candidates->contains(function (PensionnaireReference $row) use ($expectedTokens) {
            $rowTokens = $this->tokenize($row->NOM ?? null, $row->PRENOM ?? null);

            return $this->tokensMatch($expectedTokens, $rowTokens);
        });

        if ($matched) {
            return;
        }

        if ($this->strictNameMatch) {
            $details = [];

            if (filled($nom))    { $details[] = "nom « {$nom} »"; }
            if (filled($prenom)) { $details[] = "prénom « {$prenom} »"; }

            $suffix = $details !== []
                ? ' pour le ' . implode(' et le ', $details) . ' fourni(e)s'
                : '';

            $fail(
                "Le code pension « {$code} » existe dans la base de référence "
                . "mais ne correspond pas au{$suffix}."
            );
        }
    }

    /* ============================================================
     * RECHERCHE
     * ============================================================ */

    /**
     * @return Collection<int, PensionnaireReference>
     */
    protected function findByCode(string $code): Collection
    {
        return PensionnaireReference::query()
            ->whereRaw('UPPER(TRIM(PENSIONNAIRE_ID)) = ?', [$code])
            ->get();
    }

    /* ============================================================
     * EXTRACTION nom/prenom depuis le payload
     * ============================================================ */

    /**
     * @return array{0: ?string, 1: ?string}  [nom, prenom]
     */
    protected function resolveNomPrenom(): array
    {
        // 1) Champs directs (si un jour envoyés en clair)
        $directNom    = $this->data['nom']    ?? null;
        $directPrenom = $this->data['prenom'] ?? null;

        if (filled($directNom) || filled($directPrenom)) {
            return [
                filled($directNom)    ? trim((string) $directNom)    : null,
                filled($directPrenom) ? trim((string) $directPrenom) : null,
            ];
        }

        // 2) Sinon, extraire depuis ocr_documents_json
        $raw = $this->data['ocr_documents_json'] ?? null;

        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                return $this->extractFromOcrDocuments($decoded);
            }
        }

        return [null, null];
    }

    /**
     * Priorité des documents selon le statut mineur / majeur.
     *
     * @param  array<int, array<string, mixed>>  $documents
     * @return array{0: ?string, 1: ?string}
     */
    protected function extractFromOcrDocuments(array $documents): array
    {
        $isMineur = filter_var(
            $this->data['is_mineur'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        /*
         * Pour un mineur, le compte est créé au nom du mineur :
         *   → l'acte de naissance prime sur la pièce du représentant.
         * Pour un majeur :
         *   → la pièce d'identité prime.
         */
        $priority = $isMineur
            ? [
                'acte_naissance'              => 1,
                'piece_identite_representant' => 2,
                'piece_identite'              => 3,
            ]
            : [
                'piece_identite'              => 1,
                'acte_naissance'              => 2,
                'piece_identite_representant' => 3,
            ];

        $nom    = null;
        $prenom = null;
        $best   = PHP_INT_MAX;

        foreach ($documents as $doc) {
            if (! is_array($doc)) {
                continue;
            }

            $key = (string) ($doc['key'] ?? '');

            if (! isset($priority[$key])) {
                continue;
            }

            if ($priority[$key] >= $best) {
                continue;
            }

            $fields = is_array($doc['fields'] ?? null) ? $doc['fields'] : [];

            $docNom    = $this->cleanField($fields['nom']    ?? null);
            $docPrenom = $this->cleanField($fields['prenom'] ?? null);

            if ($docNom !== null || $docPrenom !== null) {
                $nom    = $docNom    ?? $nom;
                $prenom = $docPrenom ?? $prenom;
                $best   = $priority[$key];
            }
        }

        return [$nom, $prenom];
    }

    /* ============================================================
     * NORMALISATION
     * ============================================================ */

    protected function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    /**
     * Retourne les tokens normalisés (uppercase, sans accents) d'un
     * couple nom + prénom.
     *
     * @return array<int, string>  Trié, dédoublonné.
     */
    protected function tokenize(?string $nom, ?string $prenom): array
    {
        $combined = trim(((string) $nom) . ' ' . ((string) $prenom));

        if ($combined === '') {
            return [];
        }

        // Uppercase multi-octets
        $combined = mb_strtoupper($combined, 'UTF-8');

        // Suppression des accents (translittération vers ASCII)
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $combined);

            if ($converted !== false && $converted !== '') {
                $combined = $converted;
            }
        }

        // On ne garde que les caractères alphanumériques ASCII
        $combined = preg_replace('/[^A-Z0-9]+/', ' ', $combined) ?? '';

        $tokens = preg_split('/\s+/', $combined, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // Filtre des tokens trop courts (< 2 caractères) pour absorber
        // le bruit OCR (initiales parasites, ponctuation résiduelle...).
        $tokens = array_values(array_filter(
            $tokens,
            fn (string $t) => mb_strlen($t) >= 2
        ));

        $tokens = array_values(array_unique($tokens));
        sort($tokens);

        return $tokens;
    }

    /* ============================================================
     * COMPARAISON DES TOKENS
     * ============================================================ */

    /**
     * Deux ensembles de tokens matchent s'ils partagent au moins
     * `min(2, |A|, |B|)` tokens communs.
     *
     * Exemples :
     *   OCR = [JEAN, DUPONT]         | Ref = [JEAN, DUPONT]          → ✅ (2 communs)
     *   OCR = [JEAN, DUPONT]         | Ref = [JEAN, PIERRE, DUPONT]  → ✅ (2 communs)
     *   OCR = [JEAN, MARIE, DUPONT]  | Ref = [JEAN, PIERRE, DUPONT]  → ✅ (2 communs)
     *   OCR = [JEAN, DUPONT]         | Ref = [JEAN, MARTIN]          → ❌ (1 commun)
     *   OCR = [JEAN]                 | Ref = [JEAN, DUPONT]          → ✅ (1 commun)
     *   OCR = [MARIE]                | Ref = [JEAN, DUPONT]          → ❌
     */
    protected function tokensMatch(array $ocrTokens, array $refTokens): bool
    {
        if ($ocrTokens === [] || $refTokens === []) {
            return false;
        }

        $common    = array_intersect($ocrTokens, $refTokens);
        $minCommon = min(2, count($ocrTokens), count($refTokens));

        return count($common) >= $minCommon;
    }

    /* ============================================================
     * HELPERS
     * ============================================================ */

    protected function cleanField(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}