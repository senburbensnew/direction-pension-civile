<?php

namespace App\Http\Controllers\Formalites;

use App\Enums\RencontreStatutEnum;
use App\Http\Controllers\Controller;
use App\Models\AnneeFiscale;
use App\Models\User;
use App\Services\Formalites\FormaliteSuiviService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
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

        $perPage = 15;

        if ($filtre === 'remplies') {
            $formalites = $this->suivi
                ->formalitesRempliesQuery($anneeFiscaleId, $search ?: null)
                ->paginate($perPage, pageName: 'remplies_page')
                ->withQueryString();
        } elseif ($filtre === 'en_attente') {
            $demandesAttente = $this->suivi
                ->demandesEnAttenteFormaliteQuery($anneeFiscaleId)
                ->paginate($perPage, pageName: 'attente_page')
                ->withQueryString();
        } elseif ($filtre === 'a_cloturer') {
            $demandesCloture = $this->paginateCollection(
                $this->suivi->demandesACloturerFiltered(),
                $perPage,
                'cloture_page'
            );
        } elseif ($filtre === 'rdv_realises') {
            $demandesCloturesStatut = $this->paginateCollection(
                $this->suivi->demandesParStatutCloture(RencontreStatutEnum::REALISE),
                $perPage,
                'rdv_page'
            );
            $clotureStatutLabel = RencontreStatutEnum::REALISE->label();
        } elseif ($filtre === 'rdv_non_honores') {
            $demandesCloturesStatut = $this->paginateCollection(
                $this->suivi->demandesParStatutCloture(RencontreStatutEnum::NON_HONORE),
                $perPage,
                'rdv_page'
            );
            $clotureStatutLabel = RencontreStatutEnum::NON_HONORE->label();
        } elseif ($filtre === 'rdv_annules') {
            $demandesCloturesStatut = $this->paginateCollection(
                $this->suivi
                    ->demandesParStatutCloture(RencontreStatutEnum::ANNULE)
                    ->merge($this->suivi->demandesParStatutCloture(RencontreStatutEnum::REFUSE))
                    ->unique('id')
                    ->values(),
                $perPage,
                'rdv_page'
            );
            $clotureStatutLabel = 'Annulés et refusés';
        } else {
            $demandesPlanifies = $this->paginateCollection(
                $this->suivi->demandesPlanifiesFiltered(),
                $perPage,
                'planif_page'
            );
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

    /**
     * @param  Collection<int, mixed>  $items
     */
    private function paginateCollection(Collection $items, int $perPage, string $pageName): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );
    }
}
