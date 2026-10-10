<?php

namespace App\Services\AccountCreation;

use App\Enums\AccountStatusEnum;
use App\Models\DemandeCreationCompte;
use App\Models\DemandeCreationCompteHistory;
use App\Models\User;
use App\Services\Pension\BeneficiaireProvisioningService;
use App\Notifications\DemandeCreationCompteAccepteeNotification;
use App\Notifications\DemandeCreationCompteRefuseeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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
            $demande = $demande->fresh();
            $user->fill([
                'firstname' => $firstname !== '' ? $firstname : $user->firstname,
                'lastname' => $lastname !== '' ? $lastname : $user->lastname,
                'name' => $name !== '' ? $name : $user->name,
            ]);
            if ($email !== '') {
                $user->email = $email;
                $user->email_verified_at = now();
            }
            $user->account_status = AccountStatusEnum::ACTIF;
            $user->save();

            $demande->update([
                'firstname' => $firstname !== '' ? $firstname : $demande->firstname,
                'lastname' => $lastname !== '' ? $lastname : $demande->lastname,
                'email' => $email !== '' ? $email : $demande->email,
                'status' => DemandeCreationCompte::STATUS_ACCEPTEE,
                'user_id' => $user->id,
                'traitee_par' => $reviewer->id,
                'approuvee_at' => now(),
                'rejetee_at' => null,
                'refusal_reason' => null,
            ]);

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

            $provisioning = app(BeneficiaireProvisioningService::class);

            if ($demande->is_mandataire) {
                // Le mandat reste en attente d’approbation admin ; pas de lien mandataire avant approbation.
            } elseif ($demande->is_mineur) {
                $provisioning->provisionFromDemandeCreation(
                    $user->fresh(),
                    $demande,
                    is_array($demande->ocr_documents) ? $demande->ocr_documents : [],
                    true
                );
            } else {
                $provisioning->provisionAdultSelf($user->fresh(), $demande);
            }
        });

        $user = $user->fresh();
        $user->notify(new DemandeCreationCompteAccepteeNotification($demande->fresh()));

        return ['user' => $user, 'password' => null];
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

        $user = $demande->user;
        $contactEmail = $demande->email ?: $user?->email;
        $contactPhone = $demande->telephone ?: $user?->phone;

        DB::transaction(function () use ($demande, $reviewer, $reason, $user) {
            if ($user) {
                $user->update(['account_status' => AccountStatusEnum::BLOQUE]);
            }

            $demande->update([
                'status' => DemandeCreationCompte::STATUS_REFUSEE,
                'refusal_reason' => $reason,
                'traitee_par' => $reviewer->id,
                'rejetee_at' => now(),
                'approuvee_at' => null,
            ]);

            $demande->recordHistory(
                DemandeCreationCompteHistory::EVENT_REFUSEE,
                'Demande refusée. Motif : '.$reason,
                $reviewer,
                DemandeCreationCompte::STATUS_REFUSEE,
                ['user_id' => $user?->id]
            );
            $demande->recordHistory(
                DemandeCreationCompteHistory::EVENT_COMPTE_BLOQUE,
                'Compte provisoire bloqué après refus du dossier.',
                $reviewer,
                DemandeCreationCompte::STATUS_REFUSEE,
                [
                    'user_id' => $user?->id,
                    'account_status' => AccountStatusEnum::BLOQUE->value,
                ]
            );
        });

        $notification = new DemandeCreationCompteRefuseeNotification($demande->fresh(), $reason);

        if (filled($contactEmail)) {
            $routes = Notification::route('mail', $contactEmail);
            if (filled($contactPhone)) {
                $routes->route('twilio', preg_replace('/\s+/', '', trim((string) $contactPhone)));
            }
            $routes->notify($notification);
        }
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
        $rdv = $demande->rendezVousPourDecisionCompteRealise();

        if (! $rdv) {
            throw ValidationException::withMessages([
                'status' => 'Aucun rendez-vous Formalités n’est enregistré pour ce dossier.',
            ]);
        }

        if ($rdv->rencontreStatut() !== \App\Enums\RencontreStatutEnum::REALISE) {
            throw ValidationException::withMessages([
                'status' => 'La décision n’est possible qu’après un rendez-vous réalisé.',
            ]);
        }

        if (! $rdv->rencontreHeureEstPassee()) {
            $at = $rdv->rencontreScheduledAt();
            $label = $at?->format('d/m/Y à H:i') ?? 'l’heure prévue';

            throw ValidationException::withMessages([
                'status' => "La décision n’est possible qu’après l’heure du rendez-vous ({$label}).",
            ]);
        }
    }
}
