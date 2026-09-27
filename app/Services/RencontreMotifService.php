<?php

namespace App\Services;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;

class RencontreMotifService
{
    /**
     * Liste des motifs disponibles pour une demande de rendez-vous.
     */
    public function motifs(): array
    {
        return [
            'demande_formalites_mandats' =>
                'Demande de formalités de pension et mandats',
        ];
    }

    /**
     * Association entre chaque motif et son service responsable.
     */
    public function motifServiceCodes(): array
    {
        return [
            'demande_formalites_mandats' => Service::FORMALITE,
        ];
    }

    /**
     * Libellés des services.
     */
    public function serviceLabels(): array
    {
        return [
            Service::LIQUIDATION => 'Service de Liquidation',
            Service::FORMALITE => 'Service des Formalités / Accueil',
            Service::COMPTABILITE => 'Service de la Comptabilité / Paiement',
            Service::ADMINISTRATIF => 'Service Administratif / Base de données',
            Service::CONTROLE_PLACEMENT => 'Service de Contrôle',
            Service::DIRECTION => 'Direction de la Pension Civile',
            Service::SECRETARIAT => 'Secrétariat',
        ];
    }

    /**
     * Retourne le service associé à chaque motif.
     */
    public function motifServiceLabels(?User $user = null): array
    {
        $labels = [];

        foreach (array_keys($this->motifs()) as $motif) {
            $service = $this->resolve($motif, $user);

            $labels[$motif] = $service
                ? (
                    $this->serviceLabels()[$service->code]
                    ?? $service->nom
                )
                : '—';
        }

        return $labels;
    }

    /**
     * Détermine le service responsable d'un motif.
     *
     * Aucun fallback automatique vers Formalités :
     * si le motif n'est pas configuré, null est retourné.
     */
    public function resolve(string $motif, ?User $user = null): ?Service
    {
        $code = $this->motifServiceCodes()[$motif] ?? null;

        if (!$code) {
            return null;
        }

        return Service::query()
            ->where('code', $code)
            ->first();
    }

    /**
     * Vérifie qu'un motif existe dans la configuration.
     */
    public function hasMotif(string $motif): bool
    {
        return array_key_exists($motif, $this->motifs());
    }

    /**
     * Retourne le code du service associé à un motif.
     */
    public function serviceCodeForMotif(string $motif): ?string
    {
        return $this->motifServiceCodes()[$motif] ?? null;
    }

    /**
 * Retourne le libellé d'un motif à partir de sa clé.
 */
public function motifLabel(string $motif): ?string
{
    return $this->motifs()[$motif] ?? null;
}

    /**
     * Recherche le service responsable du dernier dossier actif
     * du pensionné.
     *
     * Cette méthode pourra être utilisée plus tard pour des motifs
     * comme "Suivi d'un dossier", mais elle n'intervient pas
     * actuellement dans le motif Formalités et mandats.
     */
    private function serviceFromLatestDossier(?User $user): ?Service
    {
        if (!$user) {
            return null;
        }

        $cancelledIds = WorkflowStep::query()
            ->whereIn('code', ['REJETEE', 'ANNULEE'])
            ->pluck('id');

        $dossier = Demande::query()
            ->where('created_by', $user->id)
            ->where(
                'type',
                '!=',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->whereNotNull('current_service_id')
            ->when(
                $cancelledIds->isNotEmpty(),
                function ($query) use ($cancelledIds) {
                    $query->where(function ($query) use ($cancelledIds) {
                        $query
                            ->whereNull('current_step_id')
                            ->orWhereNotIn(
                                'current_step_id',
                                $cancelledIds
                            );
                    });
                }
            )
            ->latest()
            ->first();

        return $dossier?->service;
    }
}