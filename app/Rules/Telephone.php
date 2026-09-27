<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Telephone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Format attendu :
     * +[indicatif pays][numéro]
     *
     * Exemple Haïti : +50938123456
     * Exemple France : +33612345678
     * Exemple Canada : +14161234567
     */
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        // Vérifier que la valeur est bien une chaîne
        if (!is_string($value)) {
            $fail(
                "Le champ :attribute doit être un numéro de téléphone valide."
            );

            return;
        }

        // Supprimer les espaces
        $value = preg_replace('/\s+/', '', $value);

        /**
         * Format international :
         *
         * +          → préfixe international obligatoire
         * [1-9]      → le premier chiffre de l'indicatif ne peut pas être 0
         * \d{7,14}   → chiffres supplémentaires
         *
         * Total : 8 à 15 chiffres après le +
         */
        $pattern = '/^\+[1-9]\d{7,14}$/';

        if (!preg_match($pattern, $value)) {
            $fail(
                "Le champ :attribute doit être un numéro de téléphone "
                . "international valide avec son indicatif pays "
                . "(ex. +50938123456, +33612345678 ou +14161234567)."
            );
        }
    }
}