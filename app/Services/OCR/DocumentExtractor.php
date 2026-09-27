<?php

namespace App\Services\OCR;

use App\Enums\TypeDemandeEnum;

class DocumentExtractor
{
    /**
     * @return array<string, string>
     */
    public function extractFields(string $text, string|TypeDemandeEnum $type): array
    {
        $normalized = $this->normalize($text);
        $typeValue = $type instanceof TypeDemandeEnum ? $type->value : $type;

        $extracted = [];

        foreach ($this->fieldsFor($typeValue) as $field) {
            $value = $this->extractField($normalized, $field);

            if ($value !== null && $value !== '') {
                $extracted[$field] = $value;
            }
        }

        return $extracted;
    }

    /**
     * Build a JSON-ready key/value map from identity documents (CIN, passport, permis, acte de naissance),
     * labeled OCR lines, known domain fields, and optional Document AI pairs.
     *
     * @param  array<string, string>  $extra
     * @return array<string, string>
     */
    public function extractKeyValues(
        string $text,
        string|TypeDemandeEnum|null $type = null,
        array $extra = [],
        ?string $declaredDocumentType = null,
    ): array {
        $documentType = $this->detectDocumentType($text)
            ?? ($this->isKnownDocumentType($declaredDocumentType) ? $declaredDocumentType : null);

        // ------------------------------------------------------------------
        // 1. Extraction dédiée par type de document (priorité haute).
        //    Pour un acte de naissance, on utilise le parseur narratif et on
        //    n'appelle JAMAIS extractIdentityDocument (qui écrase les valeurs).
        // ------------------------------------------------------------------
        $pairs = [];

        if ($documentType === 'acte_naissance') {
            $pairs = array_merge($pairs, $this->extractActeNaissance($text));
        } elseif ($documentType !== null) {
            $pairs = array_merge($pairs, $this->extractIdentityDocument($text, $documentType === 'permis'));
        }

        // ------------------------------------------------------------------
        // 2. Paires issues des lignes étiquetées ("LABEL : valeur"), sans
        //    écraser ce que l'extracteur dédié a déjà trouvé.
        // ------------------------------------------------------------------
        foreach ($this->pairsFromLabeledLines($text) as $key => $value) {
            if (! isset($pairs[$key])) {
                $pairs[$key] = $value;
            }
        }

        // ------------------------------------------------------------------
        // 3. MRZ (passeport)
        // ------------------------------------------------------------------
        $mrz = $this->extractMrz($text);
        if (isset($pairs['prenom'], $mrz['prenom'])) {
            $mrz['prenom'] = $this->preferSpacedName($pairs['prenom'], $mrz['prenom']);
        }
        foreach ($mrz as $key => $value) {
            if (! isset($pairs[$key])) {
                $pairs[$key] = $value;
            }
        }

        // ------------------------------------------------------------------
        // 4. Champs issus du type de demande (complètent ce qui manque)
        // ------------------------------------------------------------------
        if ($type !== null) {
            foreach ($this->extractFields($text, $type) as $key => $value) {
                if (! isset($pairs[$key])) {
                    $pairs[$key] = $value;
                }
            }
        }

        // ------------------------------------------------------------------
        // 5. Paires externes (Document AI, etc.)
        // ------------------------------------------------------------------
        foreach ($extra as $key => $value) {
            $normalizedKey = $this->slugKey((string) $key);
            $normalizedValue = trim((string) $value);

            if ($normalizedKey === '' || $normalizedValue === '') {
                continue;
            }

            $canonical = $this->canonicalField($normalizedKey) ?? $normalizedKey;
            if ($canonical === 'skip' || $canonical === 'signature') {
                continue;
            }

            $pairs[$canonical] = preg_replace('/\s+/', ' ', $normalizedValue) ?? $normalizedValue;
        }

        // ------------------------------------------------------------------
        // 6. Normalisations finales
        // ------------------------------------------------------------------
        if (isset($pairs['nif'])) {
            $pairs['nif'] = $this->formatNif($pairs['nif']) ?? $pairs['nif'];
        }

        if (isset($pairs['prenom'])) {
            $pairs['prenom'] = $this->normalizePersonName($pairs['prenom']);
        }
        if (isset($pairs['nom'])) {
            $pairs['nom'] = $this->normalizePersonName($pairs['nom']);
        }

        // ------------------------------------------------------------------
        // 7. Projection finale sur le schéma
        // ------------------------------------------------------------------
        if ($documentType !== null) {
            return $this->projectDocumentSchema($documentType, $pairs);
        }

        return $pairs;
    }

    /**
     * @return list<string>
     */
    private function documentSchema(string $type): array
    {
        return match ($type) {
            'cin' => [
                'numero_carte', 'prenom', 'nom', 'sexe', 'nationalite',
                'date_naissance', 'lieu_naissance', 'date_emission', 'date_expiration',
                'numero_identification_unique',
            ],
            'passeport' => [
                'numero_passeport', 'prenom', 'nom', 'nationalite', 'nif', 'taille',
                'numero_personnel', 'date_naissance', 'lieu_naissance', 'sexe', 'can',
                'date_emission', 'date_expiration',
            ],
            'permis' => [
                'dossier', 'nif', 'nom', 'prenom', 'adresse', 'date_naissance',
                'type', 'sexe', 'groupe_sanguin', 'lieu_emission', 'emis_le', 'expire_le',
            ],
            'acte_naissance' => [
                // Identifiants du document
                'numero_acte',
                'annee_acte',
                'registre',

                // Identité du titulaire
                'prenom',
                'nom',
                'sexe',
                'date_naissance',
                'date_naissance_texte',
                'heure_naissance',
                'lieu_naissance',

                // Filiation
                'nom_pere',
                'nom_mere',
                'nom_mere_jeune_fille',

                // Localisation
                'commune',
                'section_communale',
                'adresse_bureau',

                // Officier / témoins
                'officier_etat_civil',
                'temoins',

                // Acte administratif
                'date_acte',

                // Délivrance / copie
                'lieu_delivrance',
                'date_delivrance',
                'autorite_delivrance',
            ],
            default => [],
        };
    }

    private function isKnownDocumentType(?string $type): bool
    {
        return in_array($type, ['cin', 'passeport', 'permis', 'acte_naissance'], true);
    }

    private function detectDocumentType(string $text): ?string
    {
        if (preg_match('/acte\s+de\s+naissance|extrait\s+d[\'’ ]?acte|ak\s+nesans|birth\s+certificate/i', $text)) {
            return 'acte_naissance';
        }

        if (preg_match('/P<[A-Z0-9<]{10,}/i', $text) || preg_match('/\b(passeport|paspo|passport)\b/i', $text)) {
            return 'passeport';
        }

        if (preg_match('/permis.{0,20}conduir|permis\s+kondui|driving\s+licen/i', $text)) {
            return 'permis';
        }

        if (preg_match('/carte\s+d[\'’ ]?identification\s+nationale|kat\s+idantifikasyon|identification\s+nationale/i', $text)) {
            return 'cin';
        }

        return null;
    }

    /**
     * @param  array<string, string>  $pairs
     * @return array<string, string>
     */
    private function projectDocumentSchema(string $type, array $pairs): array
    {
        if ($type === 'cin' && isset($pairs['ninu']) && ! isset($pairs['numero_identification_unique'])) {
            $pairs['numero_identification_unique'] = $this->formatNinu($pairs['ninu']) ?? $pairs['ninu'];
        }

        if ($type === 'passeport') {
            if (! isset($pairs['numero_personnel']) && isset($pairs['nif'])) {
                $pairs['numero_personnel'] = preg_replace('/\D+/', '', $pairs['nif']) ?? $pairs['nif'];
            }
            if (! isset($pairs['nif']) && isset($pairs['numero_personnel'])) {
                $pairs['nif'] = $this->formatNif($pairs['numero_personnel']) ?? $pairs['numero_personnel'];
            }
            $pairs['date_emission'] = $pairs['date_emission'] ?? $pairs['emis_le'] ?? '';
            $pairs['date_expiration'] = $pairs['date_expiration'] ?? $pairs['expire_le'] ?? '';
            if (isset($pairs['prenom'])) {
                $pairs['prenom'] = $this->normalizePersonName($pairs['prenom']);
            }
        }

        if ($type === 'permis') {
            $pairs['emis_le'] = $pairs['emis_le'] ?? $pairs['date_emission'] ?? '';
            $pairs['expire_le'] = $pairs['expire_le'] ?? $pairs['date_expiration'] ?? '';
            $pairs['lieu_emission'] = $pairs['lieu_emission'] ?? $pairs['autorite'] ?? '';
        }

        if ($type === 'acte_naissance') {
            // Si le nom de la mère (jeune fille) est connu, on garde nom_mere aligné.
            if (! empty($pairs['nom_mere_jeune_fille']) && empty($pairs['nom_mere'])) {
                $pairs['nom_mere'] = $pairs['nom_mere_jeune_fille'];
            }
        }

        $projected = ['type_document' => $type];
        foreach ($this->documentSchema($type) as $key) {
            $value = trim((string) ($pairs[$key] ?? ''));
            if ($value !== '') {
                $projected[$key] = $value;
            }
        }

        return $projected;
    }

    /**
     * @return list<string>
     */
    private function fieldsFor(string $type): array
    {
        return match ($type) {
            TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value => [
                'nif', 'code_pension', 'nom', 'prenom', 'telephone', 'compte_bancaire',
            ],
            TypeDemandeEnum::DEMANDE_ATTESTATION->value,
            TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT->value,
            TypeDemandeEnum::DEMANDE_ARRET_VIREMENT->value,
            TypeDemandeEnum::DEMANDE_REINSERTION->value => [
                'nif', 'code_pension', 'nom', 'prenom', 'telephone',
            ],
            TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE->value,
            TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE->value => [
                'nif', 'ninu', 'code_pension', 'nom', 'prenom', 'telephone',
            ],
            TypeDemandeEnum::DEMANDE_PENSION->value,
            TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value => [
                'nif', 'ninu', 'nom', 'prenom', 'date_naissance',
            ],
            TypeDemandeEnum::DEMANDE_ETAT_CARRIERE->value => [
                'nif', 'ninu', 'nom', 'prenom',
            ],
            TypeDemandeEnum::DEMANDE_ADHESION->value => [
                'nif', 'ninu', 'email', 'telephone',
            ],
            TypeDemandeEnum::DEMANDE_CREATION_COMPTE->value => [
                'nif', 'code_pension', 'nom', 'prenom', 'email', 'telephone', 'adresse',
            ],
            default => [
                'nif', 'ninu', 'code_pension', 'nom', 'prenom', 'email', 'telephone',
            ],
        };
    }

    private function extractField(string $text, string $field): ?string
    {
        return match ($field) {
            'nif' => $this->formatNif($this->firstMatch($text, [
                '/\bNIF\b\s*[:\-]?\s*(\d{3}-\d{3}-\d{3}-\d)/i',
                '/\bNIF\b\s*[:\-]?\s*(\d{10})\b/i',
                '/\b(\d{3}-\d{3}-\d{3}-\d)\b/',
            ])),
            'ninu' => $this->firstMatch($text, [
                '/\bNINU\b\s*[:\-]?\s*(\d{3}-\d{3}-\d{3}-\d)/i',
                '/\bNIN\b\s*[:\-]?\s*(\d{3}-\d{3}-\d{3}-\d)/i',
                '/identification\s+nationale\s*[:\-]?\s*(\d{2}[-\s]?\d{2}[-\s]?\d{2}[-\s]?\d{4})/i',
            ]),
            'code_pension' => $this->firstMatch($text, [
                '/code\s*(?:de\s*)?pension\s*[:\-]?\s*([A-Z]{2,5}-?\d{4,12})/i',
            ]),
            'email' => $this->firstMatch($text, [
                '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i',
            ]),
            'telephone' => $this->normalizePhone($this->firstMatch($text, [
                '/\+?509[\s\-]?\d{8}/',
                '/\b(?:2[2-9]\d{6}|[348]\d{7})\b/',
            ])),
            'date_naissance' => $this->firstMatch($text, [
                '/(?:n[ée]e?\s+le|date\s+de\s+naissance)\s*[:\-]?\s*(\d{1,2}[\/.\-]\d{1,2}[\/.\-]\d{4})/i',
            ]),
            'adresse' => $this->firstMatch($text, [
                '/\bADRESSE\b\s*[:\-]?\s*(.+)$/im',
            ]),
            'nom' => $this->firstMatch($text, [
                '/\bNOM(?:\s+DE\s+FAMILLE)?\b\s*[:\-]?\s*([A-ZÉÈÀÙÂÊÎÔÛÇ][A-ZÉÈÀÙÂÊÎÔÛÇa-zéèàùâêîôûç\'\- ]{1,60})/u',
            ]),
            'prenom' => $this->firstMatch($text, [
                '/\bPR[EÉ]NOMS?\b\s*[:\-]?\s*([A-ZÉÈÀÙÂÊÎÔÛÇ][A-ZÉÈÀÙÂÊÎÔÛÇa-zéèàùâêîôûç\'\- ]{1,60})/u',
            ]),
            'compte_bancaire' => $this->firstMatch($text, [
                '/(?:compte|n[°o]|iban)\s*[:\-]?\s*([A-Z]{0,2}\d{8,24})/i',
            ]),
            default => null,
        };
    }

    /**
     * @param  list<string>  $patterns
     */
    private function firstMatch(string $text, array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $value = trim($matches[1] ?? $matches[0]);

                return $value !== '' ? preg_replace('/\s+/', ' ', $value) : null;
            }
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function extractIdentityDocument(string $text, bool $isLicence = false): array
    {
        $lines = $this->expandLicenceLines($this->lines($text));
        $pairs = $isLicence
            ? $this->extractLicenceGrid($lines)
            : $this->extractSequentialFields($lines, false);

        $this->splitSurnameGivenNames($pairs);

        if ($isLicence) {
            $this->rebalanceLicenceFields($pairs, $lines);
        }

        foreach (['date_naissance', 'date_emission', 'date_expiration', 'emis_le', 'expire_le'] as $dateField) {
            if (isset($pairs[$dateField])) {
                $pairs[$dateField] = $this->normalizeDate($pairs[$dateField]) ?? $pairs[$dateField];
            }
        }

        if (isset($pairs['nif'])) {
            $pairs['nif'] = $this->formatNif($pairs['nif']) ?? $pairs['nif'];
        }

        return $pairs;
    }

    /**
     * @param  list<string>  $lines
     * @return array<string, string>
     */
    private function extractSequentialFields(array $lines, bool $isLicence): array
    {
        $pairs = [];
        $count = count($lines);

        for ($i = 0; $i < $count; $i++) {
            $parsed = $this->parseLabeledLine($lines[$i], $isLicence);
            $field = $parsed['field'] ?? null;
            if ($field === null || $field === 'skip' || $field === 'signature') {
                continue;
            }

            $j = $i + 1;
            while ($j < $count && ($this->parseLabeledLine($lines[$j], $isLicence)['field'] ?? null) === $field) {
                $next = $this->parseLabeledLine($lines[$j], $isLicence);
                if (! empty($next['value'])) {
                    break;
                }
                $j++;
            }

            $values = [];
            if (! empty($parsed['value'])) {
                $values[] = $parsed['value'];
            }

            while ($j < $count) {
                $next = $this->parseLabeledLine($lines[$j], $isLicence);
                if (($next['field'] ?? null) !== null || $this->isMrzLine($lines[$j])) {
                    break;
                }
                $values[] = $lines[$j];
                $j++;
            }

            $value = $this->valueForField($field, $values);
            if ($value !== null) {
                $pairs[$field] = $value;
            }

            if ($field === 'sexe') {
                foreach ($values as $candidate) {
                    if (preg_match('/^\d{6}$/', trim($candidate))) {
                        $pairs['can'] = trim($candidate);
                    }
                }
            }

            if ($field === 'nif' && ! isset($pairs['numero_personnel'])) {
                $digits = preg_replace('/\D+/', '', implode(' ', $values)) ?? '';
                if (strlen($digits) >= 10) {
                    $pairs['numero_personnel'] = substr($digits, 0, 10);
                }
            }

            $i = max($i, $j - 1);
        }

        return $pairs;
    }

    /**
     * Haitian licences print several labels on one row, then the values on the next.
     *
     * @param  list<string>  $lines
     * @return array<string, string>
     */
    private function extractLicenceGrid(array $lines): array
    {
        $pairs = [];
        $pending = [];
        $count = count($lines);

        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];
            if ($this->isHeaderLine($line) || $this->isMrzLine($line)) {
                continue;
            }

            $labels = $this->fieldsOnLine($line);
            $parsed = $this->parseLabeledLine($line, true);
            $field = $parsed['field'] ?? null;

            if ($field === 'skip' || $field === 'signature') {
                continue;
            }

            $labelOnly = $labels !== [] && ($parsed['value'] ?? null) === null;

            if ($labelOnly) {
                $pending = array_merge($pending, $labels);

                continue;
            }

            if ($field && ($parsed['value'] ?? null) !== null) {
                if ($pending !== []) {
                    $this->assignLicenceValues($pairs, $pending, []);
                    $pending = [];
                }

                $value = $this->valueForField($field, [$parsed['value']]);
                if ($value !== null) {
                    $pairs[$field] = $value;
                }

                continue;
            }

            $tokens = $this->valueTokens($line);
            while ($i + 1 < $count && $pending !== []) {
                $next = $this->parseLabeledLine($lines[$i + 1], true);
                if (($next['field'] ?? null) !== null || $this->isHeaderLine($lines[$i + 1]) || $this->isMrzLine($lines[$i + 1])) {
                    break;
                }
                $i++;
                $tokens = array_merge($tokens, $this->valueTokens($lines[$i]));
                if (count($tokens) >= count($pending)) {
                    break;
                }
            }

            if ($pending !== []) {
                $this->assignLicenceValues($pairs, $pending, $tokens);
                $pending = [];
            }
        }

        return $pairs;
    }

    /**
     * @return list<string>
     */
    private function fieldsOnLine(string $line): array
    {
        $fields = [];
        $remaining = trim($line);

        for ($guard = 0; $guard < 8 && $remaining !== ''; $guard++) {
            [$field, $rest] = $this->matchLabelPrefix($remaining);
            if ($field === null || $field === 'skip' || $field === 'signature') {
                break;
            }

            if ($rest !== '' && ! $this->startsWithFieldLabel($rest) && ! $this->isBilingualLabelOnly($rest)) {
                if ($fields === []) {
                    return [$field];
                }

                break;
            }

            $fields[] = $field;
            $stripped = $this->remainderAfterAlias($remaining, $this->aliasForFieldOnLine($remaining, $field) ?? '');
            if ($stripped === '' || $stripped === $remaining) {
                $remaining = $this->startsWithFieldLabel($rest) || $this->isBilingualLabelOnly($rest) ? $rest : '';
            } else {
                $remaining = $stripped;
            }
            $remaining = trim($remaining, " \t/-");
        }

        return array_values(array_unique($fields));
    }

    private function startsWithFieldLabel(string $line): bool
    {
        [$field] = $this->matchLabelPrefix(trim($line));

        return $field !== null && $field !== 'skip';
    }

    private function aliasForFieldOnLine(string $line, string $field): ?string
    {
        $slug = $this->slugKey($line);
        $best = null;
        $bestLen = 0;
        foreach ($this->identityAliases()[$field] ?? [] as $alias) {
            if (($slug === $alias || str_starts_with($slug, $alias.'_')) && strlen($alias) >= $bestLen) {
                $best = $alias;
                $bestLen = strlen($alias);
            }
        }

        return $best;
    }

    /**
     * @return list<string>
     */
    private function valueTokens(string $line): array
    {
        if (preg_match_all('/\d{1,2}[\/.\-]\d{1,2}[\/.\-]\d{2,4}|(?:AB|A|B|O)\s*[+\-]|[A-Z]{2,5}(?:-[A-Z0-9]+)+|\S+/u', $line, $matches)) {
            return array_values(array_filter(array_map('trim', $matches[0])));
        }

        return $line !== '' ? [$line] : [];
    }

    /**
     * @param  array<string, string>  $pairs
     * @param  list<string>  $labels
     * @param  list<string>  $tokens
     */
    private function assignLicenceValues(array &$pairs, array $labels, array $tokens): void
    {
        $tokens = array_values(array_filter($tokens, fn (string $token) => $token !== ''));

        if (count($labels) === 1 && $tokens !== []) {
            $usable = array_values(array_filter($tokens, fn (string $token) => $this->tokenFitsField($labels[0], $token)));
            if ($usable === [] && $this->tokenFitsField($labels[0], implode(' ', $tokens))) {
                $usable = [implode(' ', $tokens)];
            }
            if ($usable !== []) {
                $joined = in_array($labels[0], ['nom', 'prenom', 'adresse'], true)
                    ? [implode(' ', $usable)]
                    : $usable;
                $value = $this->valueForField($labels[0], $joined);
                if ($value !== null) {
                    $pairs[$labels[0]] = $value;
                }
            }

            return;
        }

        foreach ($labels as $label) {
            $picked = $this->pickTokenForField($label, $tokens);
            if ($picked === null) {
                continue;
            }

            $value = $this->valueForField($label, [$picked]);
            if ($value !== null) {
                $pairs[$label] = $value;
            }
        }
    }

    /**
     * @param  list<string>  $tokens
     */
    private function pickTokenForField(string $field, array &$tokens): ?string
    {
        foreach ($tokens as $index => $token) {
            if (! $this->tokenFitsField($field, $token)) {
                continue;
            }

            unset($tokens[$index]);
            $tokens = array_values($tokens);

            return $token;
        }

        return null;
    }

    /**
     * Extraction dédiée aux actes de naissance haïtiens (Archives Nationales).
     * Le texte est narratif, donc on exploite des motifs répétitifs.
     *
     * @return array<string, string>
     */
    private function extractActeNaissance(string $text): array
    {
        $pairs = [];
        $flat = preg_replace('/\s+/u', ' ', $this->normalize($text)) ?? $text;

        // -------- Identifiants du document --------
        if (preg_match('/Acte\s*n[°ºo]?\s*:?\s*(\d+)/iu', $flat, $m)) {
            $numero = $m[1];
            $annee  = preg_match('/Ann[ée]e\s*:?\s*(\d{4})/iu', $flat, $a) ? $a[1] : null;
            $reg    = preg_match('/Registre\s*:?\s*([A-Za-z0-9]+)/iu', $flat, $r) ? $r[1] : null;

            $pairs['numero_acte'] = $annee ? "{$numero} - {$annee}" : $numero;
            if ($annee) $pairs['annee_acte'] = $annee;
            if ($reg)   $pairs['registre']   = $reg;
        }

        // -------- Prénom + NOM (en-tête "ACTE DE NAISSANCE DE Pierre Rubens MILORME") --------
        if (preg_match(
            '/ACTE\s+DE\s+NAISSANCE\s+DE\s+([\p{L}\-\' ]+?)\s+([A-ZÀ-Ý][A-ZÀ-Ý\-\']{1,}(?:\s+[A-ZÀ-Ý][A-ZÀ-Ý\-\']+)*)\s+n[ée]/u',
            $flat, $m
        )) {
            $pairs['prenom'] = trim($m[1]);
            $pairs['nom']    = trim($m[2]);
        }
        // Fallback prénoms : "il a donné les prénoms de Pierre Rubens"
        if (empty($pairs['prenom']) && preg_match('/pr[ée]noms?\s+de\s+([\p{L}\-\' ]+?)\s*[\.\,]/iu', $flat, $m)) {
            $pairs['prenom'] = trim($m[1]);
        }
        // Fallback nom : majuscules accolées aux prénoms dans l'en-tête
        if (empty($pairs['nom']) && preg_match('/ACTE\s+DE\s+NAISSANCE\s+DE\s+[\p{L}\-\' ]+?\s+([A-ZÀ-Ý\-\']{2,})/u', $flat, $m)) {
            $pairs['nom'] = trim($m[1]);
        }

        // -------- Sexe --------
        if (preg_match('/enfant\s+du\s+sexe\s+(masculin|f[ée]minin)/iu', $flat, $m)) {
            $pairs['sexe'] = strtoupper(mb_substr($m[1], 0, 1));
        }

        // -------- Date de naissance numérique : "né le 31 juillet 1990" --------
        if (preg_match('/n[ée]e?\s+le\s+(\d{1,2}\s+\p{L}+\s+\d{4})/iu', $flat, $m)) {
            $pairs['date_naissance_texte'] = trim($m[1]);
            $normalized = $this->normalizeDate(trim($m[1]));
            if ($normalized) {
                $pairs['date_naissance'] = $normalized;
            }
        }

        // -------- Date en lettres : "le trente et un juillet mil neuf cent quatre vingt dix" --------
        if (preg_match(
            '/le\s+((?:premier|un|deux|trois|quatre|cinq|six|sept|huit|neuf|dix|onze|douze|treize|quatorze|quinze|seize|vingt|trente|quarante|cinquante|soixante|et|[\s\-])+?(?:janvier|f[ée]vrier|mars|avril|mai|juin|juillet|ao[ûu]t|septembre|octobre|novembre|d[ée]cembre)[\s\-]+(?:mil\s+)?(?:neuf|deux|dix)[\p{L}\s\-]+?dix)\s+[àa]\s+/iu',
            $flat, $m
        )) {
            if (empty($pairs['date_naissance'])) {
                $fromWords = $this->frenchWordsToDate(trim($m[1]));
                if ($fromWords) {
                    $pairs['date_naissance'] = $fromWords;
                }
            }
        }

        // -------- Heure de naissance --------
        if (preg_match('/[àa]\s+([\p{L}]+)\s+heures?\s+du\s+(matin|soir|soir[ée]e|apr[èe]s-midi)/iu', $flat, $m)) {
            $pairs['heure_naissance'] = trim($m[1].' heures du '.mb_strtolower($m[2]));
        }

        // -------- Lieu de naissance --------
        if (preg_match("/n[ée]e?\s+[àa]\s+(l'[\p{L}\s\'\-\.]+?|[A-Z][\p{L}\s\'\-\.]+?)\s*,\s*le\s/iu", $flat, $m)) {
            $pairs['lieu_naissance'] = trim($m[1]);
        }

        // -------- Père --------
        if (preg_match('/le\s+sieur\s+([\p{L}\-\' ]+?)\s*,\s*majeur/iu', $flat, $m)) {
            $pairs['nom_pere'] = $this->normalizePersonName(trim($m[1]));
        }

        // -------- Mère (nom de jeune fille) --------
        if (preg_match('/[ée]pouse\s+n[ée]e\s+([\p{L}\-\' ]+?)\s*,\s*majeure/iu', $flat, $m)) {
            $pairs['nom_mere_jeune_fille'] = $this->normalizePersonName(trim($m[1]));
            $pairs['nom_mere'] = $pairs['nom_mere_jeune_fille'];
        } elseif (preg_match('/la\s+dame\s+([\p{L}\-\' ]+?)\s*,\s*majeure/iu', $flat, $m)) {
            $pairs['nom_mere'] = $this->normalizePersonName(trim($m[1]));
        }

        // -------- Commune + section --------
        if (preg_match('/commune\s+de\s+([\p{L}\-\' ]+?)(?:\s+Section\s+([\p{L}\-\' ]+?))?[\s,\.]/iu', $flat, $m)) {
            $pairs['commune'] = trim($m[1]);
            if (! empty($m[2])) {
                $pairs['section_communale'] = trim($m[2]);
            }
        }

        // -------- Date de l'acte : "dressé le 18 Décembre 1990" --------
        if (preg_match('/dress[ée]\s+le\s+(\d{1,2}\s+\p{L}+\s+\d{4})/iu', $flat, $m)) {
            $pairs['date_acte'] = $this->normalizeDate(trim($m[1])) ?? trim($m[1]);
        }

        // -------- Officier d'état civil --------
        if (preg_match('/(?:Par\s+devant\s+Nous|devant\s+Nous)\s+([\p{L}\-\' ]+?)\s*,\s*Officier/iu', $flat, $m)) {
            $pairs['officier_etat_civil'] = trim($m[1]);
        }

        // -------- Témoins --------
        if (preg_match('/en\s+pr[ée]sence\s+de\s+([\p{L}\-\' ]+?)\s+et\s+de\s+([\p{L}\-\' ]+?)\s*,/iu', $flat, $m)) {
            $pairs['temoins'] = trim($m[1]).' ; '.trim($m[2]);
        }

        // -------- Adresse du bureau --------
        if (preg_match('/(?:notre\s+Bureau|Bureau)\s*,?\s*(Rue\s+[\p{L}\s]+)/iu', $flat, $m)) {
            $pairs['adresse_bureau'] = trim($m[1]);
        }

        // -------- Délivrance de la copie : "Fait à Port-au-Prince, le 19 janvier 2015" --------
        if (preg_match('/Fait\s+[àa]\s+([\p{L}\-\' ]+?)\s*,\s*le\s+(\d{1,2}\s+\p{L}+\s+\d{4})/iu', $flat, $m)) {
            $pairs['lieu_delivrance'] = trim($m[1]);
            $pairs['date_delivrance'] = $this->normalizeDate(trim($m[2])) ?? trim($m[2]);
        }

        // -------- Autorité de délivrance --------
        if (preg_match('/([A-ZÀ-Ý][A-ZÀ-Ý\-\' ]+?)\s+DIRECTEUR\s+G[ÉE]N[ÉE]RAL/u', $flat, $m)) {
            $pairs['autorite_delivrance'] = trim($m[1]);
        }

        return array_filter($pairs, fn ($v) => $v !== null && $v !== '');
    }

    private function frenchWordsToDate(string $words): ?string
    {
        $words = mb_strtolower($words);

        $months = [
            'janvier' => 1, 'février' => 2, 'fevrier' => 2, 'mars' => 3, 'avril' => 4,
            'mai' => 5, 'juin' => 6, 'juillet' => 7, 'août' => 8, 'aout' => 8,
            'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'décembre' => 12, 'decembre' => 12,
        ];

        $monthName = null;
        $monthPos  = null;
        $monthNum  = null;
        foreach ($months as $nom => $num) {
            $pos = mb_strpos($words, $nom);
            if ($pos !== false) {
                $monthName = $nom;
                $monthPos  = $pos;
                $monthNum  = $num;
                break;
            }
        }
        if ($monthName === null) {
            return null;
        }

        $before = mb_substr($words, 0, $monthPos);
        $after  = mb_substr($words, $monthPos + mb_strlen($monthName));

        $jour  = $this->frenchWordsToInt($before);
        $annee = $this->frenchYearFromWords($after);

        if (! $jour || ! $annee) {
            return null;
        }

        return sprintf('%02d/%02d/%04d', $jour, $monthNum, $annee);
    }

    private function frenchWordsToInt(string $words): ?int
    {
        $map = [
            'premier' => 1, 'un' => 1, 'deux' => 2, 'trois' => 3, 'quatre' => 4,
            'cinq' => 5, 'six' => 6, 'sept' => 7, 'huit' => 8, 'neuf' => 9, 'dix' => 10,
            'onze' => 11, 'douze' => 12, 'treize' => 13, 'quatorze' => 14, 'quinze' => 15,
            'seize' => 16, 'vingt' => 20, 'trente' => 30, 'quarante' => 40,
            'cinquante' => 50, 'soixante' => 60,
        ];

        if (preg_match('/(\w+)\s+et\s+un/iu', $words, $m)) {
            return ($map[mb_strtolower($m[1])] ?? 0) + 1;
        }

        $total = 0;
        foreach (preg_split('/[\s\-]+/u', $words) ?: [] as $token) {
            $token = trim($token);
            if (isset($map[$token])) {
                $total += $map[$token];
            }
        }

        return $total > 0 ? $total : null;
    }

    private function frenchYearFromWords(string $words): ?int
    {
        $map = [
            'un' => 1, 'deux' => 2, 'trois' => 3, 'quatre' => 4, 'cinq' => 5, 'six' => 6,
            'sept' => 7, 'huit' => 8, 'neuf' => 9, 'dix' => 10, 'vingt' => 20,
            'trente' => 30, 'quarante' => 40, 'cinquante' => 50, 'soixante' => 60,
        ];

        if (preg_match('/mil\s+neuf\s+cent(?:\s+(.+))?$/iu', $words, $m)) {
            $rest = trim($m[1] ?? '');
            $add  = 0;
            foreach (preg_split('/[\s\-]+/u', $rest) ?: [] as $t) {
                $add += $map[$t] ?? 0;
            }
            return 1900 + $add;
        }

        if (preg_match('/deux\s+mille(?:\s+(.+))?$/iu', $words, $m)) {
            $rest = trim($m[1] ?? '');
            $add  = 0;
            foreach (preg_split('/[\s\-]+/u', $rest) ?: [] as $t) {
                $add += $map[$t] ?? 0;
            }
            return 2000 + $add;
        }

        return null;
    }

    private function tokenFitsField(string $field, string $token): bool
    {
        $token = trim($token);

        return match ($field) {
            'date_naissance', 'date_emission', 'date_expiration', 'emis_le', 'expire_le' => (bool) preg_match('/\d{1,2}[\/.\-]\d{1,2}[\/.\-]\d{2,4}/', $token),
            'sexe' => (bool) preg_match('/^[MF]$/i', $token),
            'groupe_sanguin' => (bool) preg_match('/^(AB|A|B|O|0)\s*[+\-]?$/i', $token),
            'nif' => strlen(preg_replace('/\D+/', '', $token) ?? '') >= 10,
            'type' => (bool) preg_match('/^[A-D]{1,4}$/i', $token),
            'nom', 'prenom' => $this->looksLikePersonName($token),
            'lieu_emission' => $this->looksLikePlace($token),
            'dossier' => ! $this->looksLikeNif($token) && ! $this->isLicenceJunk($token) && ! preg_match('/\d{1,2}[\/.\-]\d{1,2}[\/.\-]\d{2,4}/', $token),
            default => ! $this->looksLikeNif($token) && ! $this->isLicenceJunk($token),
        };
    }

    /**
     * @param  array<string, string>  $pairs
     */
    private function splitSurnameGivenNames(array &$pairs): void
    {
        if (empty($pairs['nom']) || ! str_contains($pairs['nom'], ',') || ! empty($pairs['prenom'])) {
            return;
        }

        [$nom, $prenom] = array_map('trim', explode(',', $pairs['nom'], 2));
        if ($nom === '' || $prenom === '') {
            return;
        }

        $pairs['nom'] = $this->normalizePersonName($nom);
        $pairs['prenom'] = $this->normalizePersonName($prenom);
    }

    /**
     * @param  array<string, string>  $pairs
     * @param  list<string>  $lines
     */
    private function rebalanceLicenceFields(array &$pairs, array $lines): void
    {
        $text = implode("\n", $lines);

        if (isset($pairs['nom']) && $this->looksLikeNif($pairs['nom'])) {
            if (empty($pairs['nif'])) {
                $pairs['nif'] = $this->formatNif($pairs['nom']) ?? $pairs['nom'];
            }
            unset($pairs['nom']);
        }

        if (empty($pairs['nif']) && preg_match('/\b(\d{3}-\d{3}-\d{3}-\d|\d{10})\b/', $text, $match)) {
            $pairs['nif'] = $this->formatNif($match[1]) ?? $match[1];
        }

        if (empty($pairs['nom']) || empty($pairs['prenom'])) {
            foreach ($lines as $line) {
                if (preg_match('/\b([A-ZÉÈÀÙ]{2,}(?:[\s\-][A-ZÉÈÀÙ]+)*)\s*,\s*([A-ZÉÈÀÙ]{2,}(?:[\s\-][A-ZÉÈÀÙ]+)*)\b/u', $line, $match)
                    && ! $this->looksLikeNif($match[1])) {
                    $pairs['nom'] = $this->normalizePersonName($match[1]);
                    $pairs['prenom'] = $this->normalizePersonName($match[2]);
                    break;
                }
            }
        }

        if (isset($pairs['groupe_sanguin'])) {
            $pairs['groupe_sanguin'] = $this->normalizeBloodType([$pairs['groupe_sanguin']]) ?? $pairs['groupe_sanguin'];
        } elseif (preg_match('/\b([ABO0])\s*([+\-])/i', $text, $match) && ! preg_match('/^[A-D]{1,4}$/i', $match[0])) {
            $pairs['groupe_sanguin'] = $this->normalizeBloodType([$match[0]]) ?? $match[0];
        }

        if (isset($pairs['emis_le']) && ! preg_match('/\d{1,2}[\/.\-]\d{1,2}[\/.\-]\d{2,4}/', $pairs['emis_le'])) {
            if (empty($pairs['lieu_emission']) && $this->looksLikePlace($pairs['emis_le'])) {
                $pairs['lieu_emission'] = $pairs['emis_le'];
            }
            unset($pairs['emis_le']);
        }

        if (isset($pairs['lieu_emission']) && ($this->isLicenceJunk($pairs['lieu_emission']) || preg_match('/\d{1,2}[\/.\-]\d{1,2}[\/.\-]\d{2,4}/', $pairs['lieu_emission']))) {
            unset($pairs['lieu_emission']);
        }

        if (empty($pairs['lieu_emission']) && preg_match('/\b(P-AU-P|PAP|PORT-AU-PRINCE|CAP-HAITIEN|JACMEL|GONAIVES|LES CAYES)\b/i', $text, $match)) {
            $pairs['lieu_emission'] = strtoupper(trim($match[1]));
        }

        $dates = [];
        if (preg_match_all('/\b(\d{1,2}\s*[\/.\-]\s*\d{1,2}\s*[\/.\-]\s*\d{2,4})\b/', $text, $matches)) {
            foreach ($matches[1] as $raw) {
                $normalized = $this->normalizeDate(preg_replace('/\s+/', '', $raw) ?? $raw);
                if ($normalized) {
                    $dates[$normalized] = $this->dateSortKey($normalized);
                }
            }
        }
        asort($dates);
        $ordered = array_keys($dates);

        $birth = $pairs['date_naissance'] ?? null;
        if ($birth === null && $ordered !== []) {
            $birth = $ordered[0];
            $pairs['date_naissance'] = $birth;
        }

        if (preg_match('/emis\s*le\b[^\d]{0,24}(\d{1,2}\s*[\/.\-]\s*\d{1,2}\s*[\/.\-]\s*\d{2,4})/i', $text, $match)) {
            $pairs['emis_le'] = $this->normalizeDate(preg_replace('/\s+/', '', $match[1]) ?? $match[1]) ?? $pairs['emis_le'] ?? null;
        }
        if (preg_match('/expire\s*le\b[^\d]{0,24}(\d{1,2}\s*[\/.\-]\s*\d{1,2}\s*[\/.\-]\s*\d{2,4})/i', $text, $match)) {
            $pairs['expire_le'] = $this->normalizeDate(preg_replace('/\s+/', '', $match[1]) ?? $match[1]) ?? $pairs['expire_le'] ?? null;
        }

        $others = array_values(array_filter($ordered, fn (string $date) => $date !== $birth));
        usort($others, fn (string $left, string $right) => $this->dateSortKey($left) <=> $this->dateSortKey($right));

        if (count($others) >= 2) {
            $pairs['emis_le'] = $others[0];
            $pairs['expire_le'] = $others[count($others) - 1];
        } elseif (count($others) === 1) {
            $pairs['emis_le'] = $others[0];
            if (($pairs['expire_le'] ?? null) === $birth || ($pairs['expire_le'] ?? null) === $others[0]) {
                unset($pairs['expire_le']);
            }
        }

        if ($birth && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $birth, $parts)) {
            if (preg_match_all('/\b'.$parts[1].'[\/.\-]'.$parts[2].'[\/.\-](\d{2,4})\b/', $text, $years)) {
                foreach ($years[1] as $year) {
                    $fullYear = (int) $year;
                    if ($fullYear < 100) {
                        $fullYear = $fullYear >= 40 ? 1900 + $fullYear : 2000 + $fullYear;
                    }
                    if ($fullYear > (int) $parts[3] + 5) {
                        $pairs['expire_le'] = sprintf('%s/%s/%04d', $parts[1], $parts[2], $fullYear);
                    }
                }
            }
        }

        if (($pairs['emis_le'] ?? null) === $birth) {
            unset($pairs['emis_le']);
        }
        if (($pairs['expire_le'] ?? null) === $birth) {
            unset($pairs['expire_le']);
        }
        if (! empty($pairs['emis_le']) && ! empty($pairs['expire_le'])
            && $this->dateSortKey($pairs['emis_le']) > $this->dateSortKey($pairs['expire_le'])) {
            [$pairs['emis_le'], $pairs['expire_le']] = [$pairs['expire_le'], $pairs['emis_le']];
        }

        $pairs = array_filter($pairs, fn ($value) => $value !== null && $value !== '');
    }

    private function looksLikeNif(string $value): bool
    {
        return strlen(preg_replace('/\D+/', '', $value) ?? '') === 10
            && ! preg_match('/[A-Z]{3,}/i', $value);
    }

    private function looksLikePersonName(string $value): bool
    {
        $value = trim($value);

        return ! $this->looksLikeNif($value)
            && ! $this->isLicenceJunk($value)
            && ! preg_match('/\d{1,2}[\/.\-]\d{1,2}[\/.\-]\d{2,4}/', $value)
            && (bool) preg_match('/[A-Za-zÉÈÀÙÂÊÎÔÛÇ]{2,}/u', $value);
    }

    private function looksLikePlace(string $value): bool
    {
        $value = trim($value);

        return ! $this->looksLikeNif($value)
            && ! $this->isLicenceJunk($value)
            && ! preg_match('/\d{1,2}[\/.\-]\d{1,2}[\/.\-]\d{2,4}/', $value)
            && (bool) preg_match('/[A-Za-z]{2,}/', $value)
            && mb_strlen($value) >= 3;
    }

    private function isLicenceJunk(string $value): bool
    {
        $slug = $this->slugKey($value);

        return in_array($slug, [
            'le', 'la', 'de', 'du', 'des', 'et', 'emis', 'expire', 'nom', 'prenom', 'nif',
            'sexe', 'type', 'sang', 'dossier', 'date', 'naissance', 'adresse', 'g', 'm',
        ], true);
    }

    private function dateSortKey(string $normalized): int
    {
        if (! preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $normalized, $match)) {
            return 0;
        }

        return ((int) $match[3]) * 10000 + ((int) $match[2]) * 100 + (int) $match[1];
    }

    /**
     * @param  list<string>  $lines
     * @return list<string>
     */
    private function expandLicenceLines(array $lines): array
    {
        $expanded = [];

        foreach ($lines as $line) {
            if (preg_match('/^(.*\b4a\b.*?)(\b4b\b.*)$/i', $line, $matches)) {
                $expanded[] = trim($matches[1]);
                $expanded[] = trim($matches[2]);
                continue;
            }

            $expanded[] = $line;
        }

        return $expanded;
    }

    /**
     * @return array{field: ?string, value: ?string}
     */
    private function parseLabeledLine(string $line, bool $isLicence = false): array
    {
        $line = trim($line);
        if ($line === '') {
            return ['field' => null, 'value' => null];
        }

        if ($this->isHeaderLine($line) || $this->isMrzLine($line)) {
            return ['field' => 'skip', 'value' => null];
        }

        $number = null;
        $stripped = $line;
        if (preg_match('/^(\d+[a-z]?)[.)\-:]\s*(.*)$/i', $line, $matches)) {
            $number = strtolower($matches[1]);
            $stripped = trim($matches[2]);
        }

        if (preg_match('/^(.{1,80}?)\s*[:：]\s*(.+)$/u', $stripped, $matches)) {
            $field = $this->fieldFromLabel($matches[1]) ?? $this->numberedLicenceField($number, $isLicence);

            return ['field' => $field, 'value' => trim($matches[2])];
        }

        [$field, $remainder] = $this->matchLabelPrefix($stripped);
        if ($field === null) {
            $field = $this->numberedLicenceField($number, $isLicence);

            return ['field' => $field, 'value' => $field ? $stripped : null];
        }

        return ['field' => $field, 'value' => $remainder !== '' ? $remainder : null];
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    private function matchLabelPrefix(string $line): array
    {
        $slug = $this->slugKey($line);
        $bestField = null;
        $bestAlias = null;
        $bestLen = 0;

        foreach ($this->identityAliases() as $field => $aliases) {
            foreach ($aliases as $alias) {
                $len = strlen($alias);
                if ($len < $bestLen) {
                    continue;
                }

                if ($slug === $alias || str_starts_with($slug, $alias.'_')) {
                    $bestField = $field;
                    $bestAlias = $alias;
                    $bestLen = $len;
                }
            }
        }

        if ($bestField === null || $bestAlias === null) {
            return [null, ''];
        }

        return [$bestField, $this->remainderAfterAlias($line, $bestAlias)];
    }

    private function remainderAfterAlias(string $line, string $alias): string
    {
        $end = 0;
        $length = mb_strlen($line);
        for ($i = 1; $i <= $length; $i++) {
            if ($this->slugKey(mb_substr($line, 0, $i)) === $alias) {
                $end = $i;
                break;
            }
        }

        $rest = trim($end > 0 ? mb_substr($line, $end) : '');
        $rest = preg_replace('/^[:：\-\s]+/u', '', $rest) ?? $rest;

        if (str_starts_with($rest, '/')) {
            $rest = trim(substr($rest, 1));
        }

        if ($rest === '' || $this->isBilingualLabelOnly($rest)) {
            return '';
        }

        $kept = [];
        $skipping = true;
        foreach (preg_split('/\s+/u', $rest) ?: [] as $token) {
            if ($skipping && $this->looksLikeLabelToken($token)) {
                continue;
            }
            $skipping = false;
            $kept[] = $token;
        }

        return trim(implode(' ', $kept));
    }

    private function isBilingualLabelOnly(string $rest): bool
    {
        if (mb_strlen(trim($rest)) <= 3 || preg_match('/\d|[+\-]/', $rest)) {
            return false;
        }

        return ! preg_match('/\b[A-Z]{3,}\b/u', $rest);
    }

    private function looksLikeLabelToken(string $token): bool
    {
        if (preg_match('/\d|[+\-]/', $token)) {
            return false;
        }

        $slug = $this->slugKey($token);
        if ($slug === '' || $this->canonicalField($slug) !== null) {
            return true;
        }

        return in_array($slug, [
            'name', 'last', 'first', 'given', 'names', 'birth', 'date', 'of', 'the', 'and', 'et',
            'de', 'du', 'des', 'la', 'le', 'issue', 'expiry', 'place', 'sex', 'blood', 'group',
            'type', 'address', 'file', 'no', 'license', 'licence',
        ], true);
    }

    private function numberedLicenceField(?string $number, bool $isLicence): ?string
    {
        if (! $isLicence || $number === null) {
            return null;
        }

        return match ($number) {
            '1' => 'nom',
            '2' => 'prenom',
            '3' => 'date_naissance',
            '4a' => 'emis_le',
            '4b' => 'expire_le',
            '4c' => 'lieu_emission',
            '5' => 'dossier',
            '8' => 'adresse',
            '9' => 'type',
            default => null,
        };
    }

    private function fieldFromLabel(string $label): ?string
    {
        [$field] = $this->matchLabelPrefix($label);

        return $field;
    }

    /**
     * @return array<string, string>
     */
    private function extractMrz(string $text): array
    {
        $mrz1 = null;
        $mrz2 = null;

        foreach ($this->lines($text) as $line) {
            $compact = strtoupper(preg_replace('/\s+/', '', $line) ?? $line);
            $compact = strtr($compact, ['«' => '<', '»' => '<']);

            if (preg_match('/^P<[A-Z0-9<]{10,}/', $compact)) {
                $mrz1 = str_pad(substr($compact, 0, 44), 44, '<');
                continue;
            }

            if ($mrz1 !== null && preg_match('/^[A-Z0-9<]{28,}$/', $compact)) {
                $mrz2 = str_pad(substr($compact, 0, 44), 44, '<');
            }
        }

        if ($mrz1 === null) {
            return [];
        }

        $pairs = ['type_document' => 'passeport'];
        $names = substr($mrz1, 5);
        $nameParts = explode('<<', $names, 2);
        $surname = $this->normalizePersonName(str_replace('<', ' ', $nameParts[0] ?? ''));
        $given = $this->normalizePersonName(str_replace('<', ' ', $nameParts[1] ?? ''));

        if ($surname !== '') {
            $pairs['nom'] = $surname;
        }
        if ($given !== '') {
            $pairs['prenom'] = $given;
        }

        if ($mrz2 === null || ! preg_match('/^([A-Z0-9<]{9})\d([A-Z0-9]{3})(\d{6})\d([MFX])(\d{6})\d([A-Z0-9<]+)/', $mrz2, $match)) {
            return $pairs;
        }

        $passport = rtrim($match[1], '<');
        if ($passport !== '') {
            $pairs['numero_passeport'] = $passport;
        }

        $nationality = strtr($match[2], ['1' => 'I', '0' => 'O']);
        if (preg_match('/^[A-Z]{3}$/', $nationality)) {
            $pairs['nationalite'] = $nationality;
        }

        $birth = $this->mrzDate($match[3]);
        if ($birth !== null) {
            $pairs['date_naissance'] = $birth;
        }

        $pairs['sexe'] = $match[4];

        $expiry = $this->mrzDate($match[5], expiry: true);
        if ($expiry !== null) {
            $pairs['date_expiration'] = $expiry;
        }

        $personal = preg_replace('/\D+/', '', str_replace('<', '', $match[6])) ?? '';
        if (strlen($personal) >= 10) {
            $personal = substr($personal, 0, 10);
            $pairs['numero_personnel'] = $personal;
            $pairs['nif'] = $this->formatNif($personal) ?? $personal;
        }

        return $pairs;
    }

    /**
     * @return array<string, string>
     */
    private function pairsFromLabeledLines(string $text): array
    {
        $pairs = [];

        foreach ($this->lines($text) as $line) {
            if (! preg_match('/^(.{2,80}?)\s*[:：]\s*(.+)$/u', $line, $matches)) {
                continue;
            }

            $label = trim($matches[1]);
            $field = $this->fieldFromLabel($label) ?? $this->slugKey($label);
            $value = trim($matches[2]);

            if ($field === '' || $field === 'skip' || $field === 'signature' || $value === '' || str_word_count($label) > 8) {
                continue;
            }

            // Évite la valeur fourre-tout du header d'acte de naissance :
            // "23- Année : 2009 - Registre: hh03"
            if (str_contains($value, ' - Année :') || str_contains($value, ' - Registre')) {
                continue;
            }

            $pairs[$field] = preg_replace('/\s+/', ' ', $value) ?? $value;
        }

        return $pairs;
    }

    /**
     * @param  list<string>  $values
     */
    private function valueForField(string $field, array $values): ?string
    {
        $filtered = [];
        foreach ($values as $value) {
            if ($this->isNoiseValue($field, $value)) {
                continue;
            }
            $filtered[] = $value;
        }

        if ($filtered === []) {
            return null;
        }

        return match ($field) {
            'prenom', 'nom', 'adresse' => preg_replace('/\s+/', ' ', implode(' ', $filtered)) ?? $filtered[0],
            'sexe' => $this->normalizeSex($filtered[0]),
            'nif' => $this->formatNif($filtered[0]) ?? $filtered[0],
            'ninu', 'numero_identification_unique' => $this->formatNinu($filtered[0]) ?? $filtered[0],
            'date_naissance', 'date_emission', 'date_expiration', 'emis_le', 'expire_le' => $this->firstDateValue($filtered),
            'type' => $this->normalizeLicenceType($filtered),
            'groupe_sanguin' => $this->normalizeBloodType($filtered),
            default => preg_replace('/\s+/', ' ', $filtered[0]) ?? $filtered[0],
        };
    }

    private function isNoiseValue(string $field, string $value): bool
    {
        $slug = $this->slugKey($value);

        if ($slug === '' || preg_match('/^\d$/', $value) || $this->isHeaderLine($value)) {
            return true;
        }

        if ($field !== 'can' && in_array($slug, ['p', 'can', 'seport', 'passeport', 'paspo', 'passport'], true)) {
            return true;
        }

        if (in_array($field, ['nom', 'prenom'], true) && in_array($slug, ['hti', 'haiti', 'ayiti'], true)) {
            return true;
        }

        if ($field === 'sexe' && ! preg_match('/^[MF]$/i', trim($value)) && ! preg_match('/\b(masculin|feminin|male|female)\b/i', $value)) {
            return strlen($value) > 3;
        }

        return false;
    }

    /**
     * @param  list<string>  $values
     */
    private function firstDateValue(array $values): ?string
    {
        foreach ($values as $value) {
            if (preg_match('/\d{1,2}[\/.\-]\d{1,2}[\/.\-]\d{2,4}/', $value, $match)) {
                return $this->normalizeDate($match[0]) ?? $match[0];
            }
        }

        return $values[0] ?? null;
    }

    /**
     * @param  list<string>  $values
     */
    private function normalizeLicenceType(array $values): ?string
    {
        foreach ($values as $value) {
            if (preg_match('/\b([A-D]{1,4}|AM|A1|A2|B1|C1|D1|BE|CE|DE)\b/i', $value, $match)) {
                return strtoupper($match[1]);
            }
        }

        $first = trim($values[0] ?? '');

        return $first !== '' ? $first : null;
    }

    /**
     * @param  list<string>  $values
     */
    private function normalizeBloodType(array $values): ?string
    {
        foreach ($values as $value) {
            if (preg_match('/\b(AB|A|B|O|0)\s*([+\-]|positif|negatif|pos|neg)/i', $value, $match)) {
                $group = strtoupper($match[1]) === '0' ? 'O' : strtoupper($match[1]);
                $negative = str_starts_with(mb_strtolower($match[2]), 'neg') || $match[2] === '-';

                return $group.($negative ? '-' : '+');
            }
        }

        $first = trim($values[0] ?? '');

        return $first !== '' ? $first : null;
    }

    private function canonicalField(string $slug): ?string
    {
        if ($slug === '') {
            return null;
        }

        $bestField = null;
        $bestLen = 0;

        foreach ($this->identityAliases() as $field => $aliases) {
            foreach ($aliases as $alias) {
                $len = strlen($alias);
                if ($len < $bestLen) {
                    continue;
                }

                if ($slug === $alias || str_starts_with($slug, $alias.'_')) {
                    $bestField = $field;
                    $bestLen = $len;
                }
            }
        }

        return $bestField;
    }

    /**
     * @return array<string, list<string>>
     */
    private function identityAliases(): array
    {
        return [
            'nom' => ['nom', 'siyati', 'surname', 'nom_de_famille', 'last_name', 'lastname', 'name'],
            'prenom' => ['prenom', 'prenoms', 'given_names', 'given_name', 'first_names', 'first_name', 'non'],
            'nif' => ['nif'],
            'numero_personnel' => ['n_personnel', 'numero_personnel', 'personal_number'],
            'ninu' => [
                'ninu', 'nin',
                'numero_d_identification_nationale',
                'numero_identification_nationale',
                'nimewo_idantifikasyon_nasyonal',
            ],
            'numero_identification_unique' => [
                'numero_d_identification_unique',
                'numero_identification_unique',
                'nimewo_idantifikasyon_inik',
            ],
            'can' => ['can'],
            'dossier' => ['dossier', 'no_dossier', 'numero_dossier', 'file_no', 'licence_no', 'license_no'],
            'adresse' => ['adresse', 'address', 'adres'],
            'type' => ['categories', 'categorie', 'category', 'type_de_permis', 'type'],
            'groupe_sanguin' => [
                'groupe_sanguin', 'group_sanguin', 'blood_group', 'blood_type',
                'gwoup_san', 'groupe_sang', 'g_sang', 'gsang',
            ],
            'lieu_emission' => [
                'lieu_d_emission', 'lieu_emission', 'place_of_issue', 'delivre_a',
                'lieu_de_delivrance', 'issued_by',
            ],
            'emis_le' => ['date_of_issue', 'emis_le', 'emise_le', 'issued_on'],
            'expire_le' => [
                'date_of_expiry', 'expiry_date', 'expire_le', 'expires', 'expiry', 'valid_until',
            ],
            'date_naissance' => [
                'date_de_naissance', 'date_of_birth', 'date_naissance', 'dat_nesans', 'dat_li_fet', 'dat_ou_fet', 'dat_kat_ou_fet',
            ],
            'lieu_naissance' => [
                'lieu_de_naissance', 'lieu_naissance', 'kote_li_fet', 'kote_ou_fet', 'kote_li_fet_lieu_de_naissance',
            ],
            'sexe' => ['sexe', 'seks', 'sex'],
            'nationalite' => ['nationalite', 'nasyonalite', 'moun_ki_peyi', 'nationality'],
            'date_emission' => [
                'date_d_emission', 'date_emission', 'dat_kat_la_fet', 'dat_paspo_a_fet', 'dat_paspo_a_feet',
            ],
            'date_expiration' => [
                'date_d_expiration', 'date_expiration', 'dat_kat_la_fini', 'dat_paspo_a_fini',
            ],
            'numero_carte' => ['numero_de_carte', 'nimewo_kat_la', 'nimewo_kat'],
            'numero_passeport' => ['paspo_nimewo', 'n_passeport', 'numero_passeport', 'passeport_n'],
            'taille' => ['taille', 'wote'],
            'autorite' => ['autorite', 'otorite'],
            'numero_acte' => ['numero_d_acte', 'numero_acte', 'n_acte', 'acte_n', 'nimewo_ak'],
            'nom_pere' => ['nom_du_pere', 'nom_pere', 'pere', 'father'],
            'nom_mere' => ['nom_de_la_mere', 'nom_mere', 'mere', 'mother'],
            'commune' => ['commune', 'komin'],
            'date_acte' => ['date_de_l_acte', 'date_acte', 'dresse_le', 'dat_ak'],
            'signature' => [
                'signature', 'signature_du_titulaire', 'siyati_met_kat_la', 'siyali_met_paspo_a',
                'siyati_met_paspo_a', 'de_la_titulaire',
            ],
            'annee_acte' => ['annee', 'annee_acte', 'an_acte'],
            'registre' => ['registre', 'registre_acte', 'rejis'],
            'date_naissance_texte' => ['date_naissance_en_lettres', 'date_naissance_texte'],
            'heure_naissance' => ['heure_de_naissance', 'heure_naissance', 'leure_nesans'],
            'nom_mere_jeune_fille' => ['nom_mere_jeune_fille', 'nom_de_jeune_fille', 'jeune_fille'],
            'section_communale' => ['section_communale', 'section', 'seksyon'],
            'adresse_bureau' => ['adresse_bureau', 'bureau', 'adresse_de_l_officier'],
            'officier_etat_civil' => ['officier', 'officier_etat_civil', 'ofisye_leta_sivil'],
            'temoins' => ['temoins', 'temoins_choisis', 'temwen'],
            'lieu_delivrance' => ['lieu_de_delivrance', 'lieu_delivrance', 'fait_a'],
            'date_delivrance' => ['date_de_delivrance', 'date_delivrance', 'delivre_le'],
            'autorite_delivrance' => ['autorite', 'autorite_de_delivrance', 'directeur_general'],
        ];
    }

    private function isHeaderLine(string $line): bool
    {
        $slug = $this->slugKey($line);

        return in_array($slug, [
            'republique_d_haiti', 'republik_dayiti',
            'carte_d_identification_nationale', 'kat_idantifikasyon_nasyonal',
            'passeport', 'paspo', 'passport', 'ayiti_haiti', 'haiti', 'ayiti',
            'permis_de_conduire', 'permis_kondui', 'driving_licence', 'driving_license',
            'acte_de_naissance', 'extrait_d_acte_de_naissance',
            'cs_scanned_with_camscanner', 'scanned_with_camscanner',
        ], true) || str_contains($slug, 'scanned_with');
    }

    private function isMrzLine(string $line): bool
    {
        $compact = strtoupper(preg_replace('/\s+/', '', $line) ?? $line);

        return str_starts_with($compact, 'P<') || substr_count($compact, '<') >= 4;
    }

    /**
     * @return list<string>
     */
    private function lines(string $text): array
    {
        $lines = preg_split('/\R/u', $this->normalize($text)) ?: [];

        return array_values(array_filter(array_map('trim', $lines), fn (string $line) => $line !== ''));
    }

    private function slugKey(string $label): string
    {
        $value = mb_strtolower(trim($label));
        $value = strtr($value, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'í' => 'i',
            'ô' => 'o', 'ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'õ' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ]);
        $value = preg_replace('/[:："°]+/', ' ', $value) ?? $value;
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? $value;

        return trim($value, '_');
    }

    private function normalize(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return preg_replace('/[ \t]+/', ' ', $text) ?? $text;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '509') && strlen($digits) === 11) {
            return '+'.$digits;
        }

        if (strlen($digits) === 8) {
            return '+509'.$digits;
        }

        return $phone;
    }

    private function normalizeSex(string $value): string
    {
        $value = strtoupper(trim($value));

        if (str_starts_with($value, 'F') || str_contains(mb_strtolower($value), 'fem')) {
            return 'F';
        }

        return 'M';
    }

    private function formatNif(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits !== '' ? $digits : null;
    }

    private function normalizePersonName(string $value): string
    {
        $value = strtr($value, ['<' => ' ', '«' => ' ', '»' => ' ']);
        $parts = preg_split('/\s+/u', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return implode(' ', $parts);
    }

    private function preferSpacedName(string $visual, string $mrz): string
    {
        $visual = $this->normalizePersonName($visual);
        $mrz = $this->normalizePersonName($mrz);
        $visualParts = preg_split('/\s+/u', $visual, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $mrzParts = preg_split('/\s+/u', $mrz, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($visualParts === [] && $mrzParts === []) {
            return '';
        }

        $compactVisual = implode('', $visualParts);
        $compactMrz = implode('', $mrzParts);

        if (strcasecmp($compactVisual, $compactMrz) === 0) {
            return count($visualParts) >= 2 ? $visual : ($mrz !== '' ? $mrz : $visual);
        }

        if (count($visualParts) >= 2 && count($mrzParts) <= 1) {
            return $visual;
        }

        if (count($mrzParts) >= 2) {
            return $mrz;
        }

        return $visualParts[0] ?? ($mrzParts[0] ?? '');
    }

    private function formatNinu(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits !== '' ? $digits : null;
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);

        if (preg_match('/^(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{2,4})$/', $value, $match)) {
            return $this->formatDayMonthYear((int) $match[1], (int) $match[2], (int) $match[3]);
        }

        if (preg_match('/^(\d{1,2})[\/.\-]([A-Za-z]{3,})[\/.\-](\d{2,4})$/', $value, $match)) {
            $month = $this->monthNumber($match[2]);

            return $month ? $this->formatDayMonthYear((int) $match[1], $month, (int) $match[3]) : $value;
        }

        if (preg_match('/^(\d{1,2})\s+([A-Za-z]{3,})(?:\s*\/\s*[A-Za-z]+)?\s+(\d{2,4})$/u', $value, $match)) {
            $month = $this->monthNumber($match[2]);

            return $month ? $this->formatDayMonthYear((int) $match[1], $month, (int) $match[3]) : $value;
        }

        return $value;
    }

    private function mrzDate(string $yymmdd, bool $expiry = false): ?string
    {
        if (! preg_match('/^(\d{2})(\d{2})(\d{2})$/', $yymmdd, $match)) {
            return null;
        }

        $year = (int) $match[1];
        $month = (int) $match[2];
        $day = (int) $match[3];

        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return null;
        }

        $fullYear = $expiry || $year < 40 ? 2000 + $year : 1900 + $year;

        return sprintf('%02d/%02d/%04d', $day, $month, $fullYear);
    }

    private function formatDayMonthYear(int $day, int $month, int $year): ?string
    {
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return null;
        }

        if ($year < 100) {
            $year = $year >= 40 ? 1900 + $year : 2000 + $year;
        }

        return sprintf('%02d/%02d/%04d', $day, $month, $year);
    }

    private function monthNumber(string $token): ?int
    {
        $slug = $this->slugKey($token);

        $months = [
            'jan' => 1, 'janv' => 1, 'january' => 1,
            'fev' => 2, 'feb' => 2, 'fevr' => 2, 'february' => 2,
            'mar' => 3, 'mars' => 3, 'march' => 3,
            'avr' => 4, 'apr' => 4, 'avril' => 4, 'april' => 4,
            'mai' => 5, 'may' => 5,
            'jun' => 6, 'juin' => 6, 'june' => 6,
            'jiy' => 7, 'juil' => 7, 'jul' => 7, 'july' => 7,
            'aou' => 8, 'aug' => 8, 'aout' => 8, 'august' => 8,
            'sep' => 9, 'sept' => 9, 'september' => 9,
            'oct' => 10, 'october' => 10, 'octobre' => 10,
            'nov' => 11, 'november' => 11, 'novembre' => 11,
            'dec' => 12, 'december' => 12, 'decembre' => 12,
        ];

        foreach ($months as $name => $number) {
            if ($slug === $name || str_starts_with($slug, $name)) {
                return $number;
            }
        }

        return null;
    }
}