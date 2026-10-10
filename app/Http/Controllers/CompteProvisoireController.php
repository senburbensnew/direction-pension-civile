<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatusEnum;
use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CompteProvisoireController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $demande = $user->demandeCreationCompte;
        $rdv = $demande?->rendezVousFinalisation();
        $rdvData = is_array($rdv?->data) ? $rdv->data : [];

        $rdvStatut = RencontreStatutEnum::tryFrom((string) ($rdvData['rdv_statut'] ?? ''));
        $rdvAnnule = in_array($rdvStatut, [
            RencontreStatutEnum::ANNULE,
            RencontreStatutEnum::REFUSE,
            RencontreStatutEnum::NON_HONORE,
        ], true);

        $rdvRealise = (bool) $demande?->hasRendezVousRealise();
        $hasRdvActif = $rdv !== null && ! $rdvAnnule;
        $needsRdv = $demande?->isPending()
            && $user->hasPensionPortalAccess()
            && ! $rdvRealise
            && ! $hasRdvActif;

        $accountStatus = $user->resolvedAccountStatus();
        $isRefused = $demande?->status === DemandeCreationCompte::STATUS_REFUSEE
            || $accountStatus === AccountStatusEnum::BLOQUE;

        return view('compte.provisoire', [
            'user' => $user,
            'demande' => $demande,
            'rdv' => $hasRdvActif ? $rdv : null,
            'rdvStatut' => $rdvStatut,
            'rdvSummary' => $this->rdvSummary($rdv, $rdvData, $rdvStatut),
            'steps' => $this->journeySteps($demande, $hasRdvActif, $rdvRealise, $isRefused),
            'needsRdv' => $needsRdv,
            'rdvRealise' => $rdvRealise,
            'awaitingDecision' => (bool) ($demande?->canBeDecided()),
            'isRefused' => $isRefused,
            'canBookRdv' => $user->hasPensionPortalAccess() && ! $isRefused,
            'rendezVousDemandes' => Demande::query()
                ->where('created_by', $user->id)
                ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                ->latest()
                ->get(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $rdvData
     * @return array{date: ?string, heure: ?string, modalite: ?string, label: ?string}
     */
    private function rdvSummary(?Demande $rdv, array $rdvData, ?RencontreStatutEnum $statut): array
    {
        if (! $rdv) {
            return ['date' => null, 'heure' => null, 'modalite' => null, 'label' => null];
        }

        $dateRaw = $rdvData['date_souhaitee'] ?? null;
        $date = null;

        if (filled($dateRaw)) {
            try {
                $date = Carbon::parse((string) $dateRaw)->locale('fr')->isoFormat('dddd D MMMM YYYY');
            } catch (\Throwable) {
                $date = (string) $dateRaw;
            }
        }

        $heure = filled($rdvData['heure_souhaitee'] ?? null)
            ? (string) $rdvData['heure_souhaitee']
            : null;

        $modalite = match ($rdvData['modalite'] ?? null) {
            'visio' => 'Visioconférence',
            'physique' => 'Présentiel',
            default => filled($rdvData['modalite'] ?? null)
                ? ucfirst((string) $rdvData['modalite'])
                : null,
        };

        return [
            'date' => $date,
            'heure' => $heure,
            'modalite' => $modalite,
            'label' => $statut?->label(),
        ];
    }

    /**
     * @return list<array{key: string, title: string, hint: string, state: string}>
     */
    private function journeySteps(
        ?DemandeCreationCompte $demande,
        bool $hasRdvActif,
        bool $rdvRealise,
        bool $isRefused
    ): array {
        if ($isRefused) {
            return [
                [
                    'key' => 'submitted',
                    'title' => 'Demande enregistrée',
                    'hint' => 'Votre dossier a été transmis à la Direction.',
                    'state' => 'done',
                ],
                [
                    'key' => 'refused',
                    'title' => 'Dossier refusé',
                    'hint' => 'Consultez le motif dans votre demande de compte.',
                    'state' => 'current',
                ],
            ];
        }

        $submitted = $demande !== null;

        $steps = [
            [
                'key' => 'submitted',
                'title' => 'Demande enregistrée',
                'hint' => $submitted
                    ? 'Référence '.$demande->code.'.'
                    : 'Aucune demande associée.',
                'state' => $submitted ? 'done' : 'current',
            ],
            [
                'key' => 'rdv',
                'title' => 'Rendez-vous Formalités',
                'hint' => $rdvRealise
                    ? 'Rendez-vous marqué comme réalisé.'
                    : ($hasRdvActif
                        ? 'Votre créneau est réservé — présentez-vous avec vos pièces.'
                        : 'Prenez rendez-vous pour finaliser l’ouverture du compte.'),
                'state' => $rdvRealise ? 'done' : ($hasRdvActif ? 'current' : ($submitted ? 'current' : 'upcoming')),
            ],
            [
                'key' => 'review',
                'title' => 'Validation du compte',
                'hint' => $rdvRealise
                    ? 'Le Service Formalités examine votre dossier après le rendez-vous.'
                    : 'Disponible une fois le rendez-vous réalisé.',
                'state' => $rdvRealise ? 'current' : 'upcoming',
            ],
            [
                'key' => 'active',
                'title' => 'Compte actif',
                'hint' => 'Accès complet aux démarches pension en ligne.',
                'state' => 'upcoming',
            ],
        ];

        if ($rdvRealise) {
            $steps[1]['state'] = 'done';
            $steps[2]['state'] = 'current';
        } elseif ($hasRdvActif) {
            $steps[1]['state'] = 'current';
        } elseif ($submitted) {
            $steps[1]['state'] = 'current';
        }

        return $steps;
    }
}
