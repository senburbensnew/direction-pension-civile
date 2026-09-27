<?php

namespace App\Services\AccountCreation;

use App\Models\DemandeCreationCompte;
use App\Models\DemandeCreationCompteHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DemandeCreationCompteReviewService
{
    /**
     * @param  array{firstname?: string, lastname?: string, email?: string}  $overrides
     * @return array{user: User, password: ?string}
     */
    public function accept(DemandeCreationCompte $demande, User $reviewer, array $overrides = []): array
    {
        $this->assertPending($demande);
        $this->assertRendezVousRealise($demande);

        $user = $demande->user;
        if (! $user) {
            throw ValidationException::withMessages([
                'user' => 'Aucun compte provisoire n’est lié à cette demande.',
            ]);
        }

        $firstname = trim((string) ($overrides['firstname'] ?? $demande->firstname ?? ''));
        $lastname = trim((string) ($overrides['lastname'] ?? $demande->lastname ?? ''));
        $email = mb_strtolower(trim((string) ($overrides['email'] ?? $demande->email ?? $user->email ?? '')));

        if ($email !== '' && User::query()->where('id', '!=', $user->id)->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Cette adresse e-mail est déjà utilisée.',
            ]);
        }

        $name = trim($firstname.' '.$lastname);

        DB::transaction(function () use ($demande, $reviewer, $user, $firstname, $lastname, $name, $email) {
            $user->fill([
                'firstname' => $firstname !== '' ? $firstname : $user->firstname,
                'lastname' => $lastname !== '' ? $lastname : $user->lastname,
                'name' => $name !== '' ? $name : $user->name,
            ]);
            if ($email !== '') {
                $user->email = $email;
            }
            $user->account_status = User::STATUS_ACTIF;
            $user->save();

            $demande->update([
                'firstname' => $firstname !== '' ? $firstname : $demande->firstname,
                'lastname' => $lastname !== '' ? $lastname : $demande->lastname,
                'email' => $email !== '' ? $email : $demande->email,
                'status' => DemandeCreationCompte::STATUS_ACCEPTEE,
                'user_id' => $user->id,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'refusal_reason' => null,
            ]);

            $demande->purgeIdentityDocuments();

            $demande->recordHistory(
                DemandeCreationCompteHistory::EVENT_ACCEPTEE,
                'Dossier accepté après rendez-vous. Le compte est activé.',
                $reviewer,
                DemandeCreationCompte::STATUS_ACCEPTEE,
                ['user_id' => $user->id]
            );
            $demande->recordHistory(
                DemandeCreationCompteHistory::EVENT_COMPTE_ACTIVE,
                'Compte activé après acceptation du dossier.',
                $reviewer,
                DemandeCreationCompte::STATUS_ACCEPTEE,
                ['user_id' => $user->id]
            );
            $demande->recordHistory(
                DemandeCreationCompteHistory::EVENT_PIECES_SUPPRIMEES,
                'Pièces d’identité supprimées après acceptation pour libérer l’espace.',
                $reviewer,
                DemandeCreationCompte::STATUS_ACCEPTEE,
                ['fichiers_effaces' => true]
            );
        });

        return ['user' => $user->fresh(), 'password' => null];
    }

    public function refuse(DemandeCreationCompte $demande, User $reviewer, string $reason): void
    {
        $this->assertPending($demande);
        $this->assertRendezVousRealise($demande);

        $reason = trim($reason);
        if (mb_strlen($reason) < 5) {
            throw ValidationException::withMessages([
                'refusal_reason' => 'Indiquez un motif de refus (au moins 5 caractères).',
            ]);
        }

        DB::transaction(function () use ($demande, $reviewer, $reason) {
            $user = $demande->user;

            $demande->update([
                'status' => DemandeCreationCompte::STATUS_REFUSEE,
                'refusal_reason' => $reason,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);
            $demande->recordHistory(
                DemandeCreationCompteHistory::EVENT_REFUSEE,
                'Demande refusée. Motif : '.$reason,
                $reviewer,
                DemandeCreationCompte::STATUS_REFUSEE,
                ['user_id' => $user?->id]
            );
            $demande->recordHistory(
                DemandeCreationCompteHistory::EVENT_COMPTE_SUPPRIME,
                'Compte provisoire et informations personnelles supprimés.',
                $reviewer,
                DemandeCreationCompte::STATUS_REFUSEE,
                ['user_id' => $user?->id, 'donnees_effacees' => true]
            );
            $demande->purgePersonalData();
            $user?->delete();
        });
    }

    private function assertPending(DemandeCreationCompte $demande): void
    {
        if (! $demande->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Cette demande a déjà été traitée.',
            ]);
        }
    }

    private function assertRendezVousRealise(DemandeCreationCompte $demande): void
    {
        if (! $demande->hasRendezVousRealise()) {
            throw ValidationException::withMessages([
                'status' => 'La décision n’est possible qu’après un rendez-vous réalisé.',
            ]);
        }
    }
}
