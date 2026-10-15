<?php

namespace App\Http\Controllers\Formalites;

use App\Enums\RencontreStatutEnum;
use App\Http\Controllers\Controller;
use App\Models\AnneeFiscale;
use App\Models\User;
use App\Services\Formalites\FormaliteSuiviService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormaliteSuiviController extends Controller
{
    public function __construct(
        private FormaliteSuiviService $suivi
    ) {}

    public function index(Request $request): View
    {
        $filtre = $request->string('filtre')->toString() ?: 'en_attente';
        $anneeFiscaleId = $request->filled('annee_fiscale_id')
            ? (int) $request->input('annee_fiscale_id')
            : AnneeFiscale::active()?->id;

        $search = $request->string('q')->trim()->toString();
        $stats = $this->suivi->stats($anneeFiscaleId);
        $annees = AnneeFiscale::ordered();

        $filtreLabels = [
            'remplies' => 'Formalités remplies',
            'en_attente' => 'En attente d’enregistrement',
            'a_cloturer' => 'RDV à clôturer',
            'rdv_planifies' => 'RDV à venir',
            'rdv_realises' => 'RDV réalisés',
            'rdv_non_honores' => 'RDV non honorés',
            'rdv_annules' => 'RDV annulés / refusés',
        ];

        $formalites = null;
        $demandesAttente = null;
        $demandesCloture = null;
        $demandesPlanifies = null;
        $demandesCloturesStatut = null;
        $clotureStatutLabel = null;

        if ($filtre === 'remplies') {
            $formalites = $this->suivi
                ->formalitesRempliesQuery($anneeFiscaleId, $search ?: null)
                ->paginate(20)
                ->withQueryString();
        } elseif ($filtre === 'en_attente') {
            $demandesAttente = $this->suivi
                ->demandesEnAttenteFormaliteQuery($anneeFiscaleId)
                ->paginate(20, pageName: 'attente_page')
                ->withQueryString();
        } elseif ($filtre === 'a_cloturer') {
            $demandesCloture = $this->suivi->demandesACloturerFiltered();
        } elseif ($filtre === 'rdv_realises') {
            $demandesCloturesStatut = $this->suivi->demandesParStatutCloture(
                RencontreStatutEnum::REALISE
            );
            $clotureStatutLabel = RencontreStatutEnum::REALISE->label();
        } elseif ($filtre === 'rdv_non_honores') {
            $demandesCloturesStatut = $this->suivi->demandesParStatutCloture(
                RencontreStatutEnum::NON_HONORE
            );
            $clotureStatutLabel = RencontreStatutEnum::NON_HONORE->label();
        } elseif ($filtre === 'rdv_annules') {
            $demandesCloturesStatut = $this->suivi
                ->demandesParStatutCloture(RencontreStatutEnum::ANNULE)
                ->merge($this->suivi->demandesParStatutCloture(RencontreStatutEnum::REFUSE))
                ->unique('id')
                ->values();
            $clotureStatutLabel = 'Annulés et refusés';
        } else {
            $demandesPlanifies = $this->suivi->demandesPlanifiesFiltered();
        }

        return view('formalites.suivi.index', compact(
            'filtre',
            'filtreLabels',
            'stats',
            'annees',
            'anneeFiscaleId',
            'search',
            'formalites',
            'demandesAttente',
            'demandesCloture',
            'demandesPlanifies',
            'demandesCloturesStatut',
            'clotureStatutLabel',
        ));
    }

    public static function accessRoles(): array
    {
        return [
            'responsable_service_accueil_formalites',
            User::ROLE_VALIDATEUR_RDV,
            User::ROLE_AGENT_FORMALITES,
            'admin',
            User::ROLE_DIRECTEUR,
            User::ROLE_ASSISTANT_DIRECTEUR,
        ];
    }
}
