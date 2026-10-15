<?php

namespace App\Services\Formalites;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\AnneeFiscale;
use App\Models\Demande;
use App\Models\Formalite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FormaliteSuiviService
{
    /**
     * @return array{
     *     remplies: int,
     *     en_attente: int,
     *     a_cloturer: int,
     *     rdv_planifies: int,
     *     rdv_realises: int,
     *     rdv_non_honores: int,
     *     rdv_annules: int
     * }
     */
    public function stats(?int $anneeFiscaleId = null): array
    {
        $anneeFiscaleId ??= AnneeFiscale::active()?->id;

        $remplies = Formalite::query()
            ->when($anneeFiscaleId, fn (Builder $q) => $q->where('annee_fiscale_id', $anneeFiscaleId))
            ->count();

        $demandes = $this->formaliteRencontreQuery()->get();

        $enAttente = 0;
        $aCloturer = 0;
        $planifies = 0;
        $realises = 0;
        $nonHonores = 0;
        $annules = 0;

        foreach ($demandes as $demande) {
            $statut = $demande->rencontreStatutMetier();

            if ($statut->isRdvHonore()) {
                $realises++;

                if ($anneeFiscaleId && ! $this->ownerHasFormaliteForYear($demande, $anneeFiscaleId)) {
                    $enAttente++;
                } elseif (! $anneeFiscaleId && ! $this->ownerHasAnyFormalite($demande)) {
                    $enAttente++;
                }

                continue;
            }

            if ($statut->isRdvNonHonore()) {
                $nonHonores++;

                continue;
            }

            if ($statut->isRdvAnnule()) {
                $annules++;

                continue;
            }

            if ($statut->isTerminal()) {
                continue;
            }

            if ($demande->doitApparaitreCommeACloturer()) {
                $aCloturer++;
            } elseif (! $demande->rencontreHeureEstPassee()) {
                $planifies++;
            }
        }

        return [
            'remplies' => $remplies,
            'en_attente' => $enAttente,
            'a_cloturer' => $aCloturer,
            'rdv_planifies' => $planifies,
            'rdv_realises' => $realises,
            'rdv_non_honores' => $nonHonores,
            'rdv_annules' => $annules,
        ];
    }

    public function formaliteRencontreQuery(): Builder
    {
        return Demande::query()
            ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->where(function (Builder $q) {
                $q->where('data->motif_key', 'like', 'demande_formalites%')
                    ->orWhere('data->motif_key', 'like', 'formalites%');
            });
    }

    public function formalitesRempliesQuery(?int $anneeFiscaleId = null, ?string $search = null): Builder
    {
        return Formalite::query()
            ->with(['user', 'agent', 'anneeFiscale', 'demande'])
            ->when($anneeFiscaleId, fn (Builder $q) => $q->where('annee_fiscale_id', $anneeFiscaleId))
            ->when(filled($search), function (Builder $q) use ($search) {
                $term = '%'.$search.'%';
                $q->where(function (Builder $inner) use ($term) {
                    $inner->whereHas('user', fn (Builder $u) => $u
                        ->where('firstname', 'like', $term)
                        ->orWhere('lastname', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('username', 'like', $term))
                        ->orWhereHas('anneeFiscale', fn (Builder $a) => $a
                            ->where('code', 'like', $term)
                            ->orWhere('libelle', 'like', $term));
                });
            })
            ->orderByDesc('realisee_at');
    }

    public function demandesEnAttenteFormaliteQuery(?int $anneeFiscaleId = null): Builder
    {
        $anneeFiscaleId ??= AnneeFiscale::active()?->id;

        return $this->formaliteRencontreQuery()
            ->with(['user'])
            ->whereEffectiveRencontreStatut(RencontreStatutEnum::REALISE)
            ->when($anneeFiscaleId, function (Builder $q) use ($anneeFiscaleId) {
                $q->whereNotExists(function ($sub) use ($anneeFiscaleId) {
                    $sub->selectRaw('1')
                        ->from('formalites')
                        ->where('formalites.annee_fiscale_id', $anneeFiscaleId)
                        ->whereColumn('formalites.user_id', 'demandes.created_by');
                });
            })
            ->orderByDesc('updated_at');
    }

    public function demandesACloturerQuery(): Builder
    {
        return $this->formaliteRencontreQuery()
            ->with(['user'])
            ->whereEffectiveRencontreStatutOpen()
            ->orderByDesc('updated_at');
    }

    /** @return Collection<int, Demande> */
    public function demandesACloturerFiltered(): Collection
    {
        return $this->demandesACloturerQuery()
            ->get()
            ->filter(fn (Demande $d) => $d->doitApparaitreCommeACloturer())
            ->values();
    }

    /** @return Collection<int, Demande> */
    public function demandesPlanifiesFiltered(): Collection
    {
        return $this->formaliteRencontreQuery()
            ->with(['user'])
            ->whereEffectiveRencontreStatutOpen()
            ->orderBy('data->date_souhaitee')
            ->get()
            ->filter(fn (Demande $d) => ! $d->rencontreHeureEstPassee())
            ->values();
    }

    /**
     * @return Collection<int, Demande>
     */
    public function demandesParStatutCloture(RencontreStatutEnum $statut): Collection
    {
        $demandes = $this->formaliteRencontreQuery()
            ->with(['user'])
            ->orderByDesc('updated_at')
            ->get();

        return $demandes->filter(function (Demande $demande) use ($statut) {
            $metier = $demande->rencontreStatutMetier();

            return match ($statut) {
                RencontreStatutEnum::REALISE => $metier->isRdvHonore(),
                RencontreStatutEnum::NON_HONORE => $metier->isRdvNonHonore(),
                RencontreStatutEnum::ANNULE => $demande->rencontreStatut() === RencontreStatutEnum::ANNULE,
                RencontreStatutEnum::REFUSE => $demande->rencontreStatut() === RencontreStatutEnum::REFUSE,
                default => false,
            };
        })->values();
    }

    public function ownerHasFormaliteForYear(Demande $demande, int $anneeFiscaleId): bool
    {
        $ownerId = (int) ($demande->created_by ?? 0);

        if ($ownerId <= 0) {
            return false;
        }

        return Formalite::forYear($ownerId, $anneeFiscaleId) !== null;
    }

    private function ownerHasAnyFormalite(Demande $demande): bool
    {
        $ownerId = (int) ($demande->created_by ?? 0);

        if ($ownerId <= 0) {
            return false;
        }

        return Formalite::query()->where('user_id', $ownerId)->exists();
    }
}
