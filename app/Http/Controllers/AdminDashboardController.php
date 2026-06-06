<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Contact;
use App\Models\Demande;
use App\Models\DemandeWorkflow;
use App\Models\Newsletter;
use App\Models\Report;
use App\Models\Service;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users'           => User::count(),
            'services'        => Service::count(),
            'roles'           => Role::count(),
            'permissions'     => Permission::count(),
            'actualites'      => Actualite::count(),
            'reports'         => Report::count(),
            'newsletter'      => Newsletter::count(),
            'contacts'        => Contact::count(),
            'contacts_unread' => Contact::where('read', false)->count(),
        ];

        // --- Métriques dossiers ---
        $terminalCodes = ['BROUILLON', 'APPROUVEE', 'REJETEE', 'CLOTUREE', 'ANNULEE'];
        $terminalIds   = Status::whereIn('code', $terminalCodes)->pluck('id');

        $dossierStats = [
            'total'             => Demande::count(),
            'en_cours'          => Demande::whereNotIn('status_id', $terminalIds)->count(),
            'urgents'           => Demande::whereNotIn('status_id', $terminalIds)->where('is_urgent', true)->count(),
            'delai_legal'       => Demande::whereNotIn('status_id', $terminalIds)
                                    ->whereNotNull('submitted_at')
                                    ->where('submitted_at', '<=', now()->subDays(30))
                                    ->count(),
            'en_attente_recep'  => Demande::whereHas('status', fn($q) => $q->where('code', 'TRANSFERT_EN_ATTENTE'))->count(),
        ];

        // Charge par service (dossiers actifs par service courant)
        $chargeParService = Service::withCount([
            'demandes as dossiers_actifs' => fn($q) => $q->whereNotIn('status_id', $terminalIds),
        ])->orderByDesc('dossiers_actifs')->get();

        // Dossiers par statut
        $dossierParStatut = Status::withCount('demandes')
            ->having('demandes_count', '>', 0)
            ->orderByDesc('demandes_count')
            ->get();

        // Taux de refus de réception (30 derniers jours)
        $tauxRefus = DemandeWorkflow::where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('reception_status')
            ->selectRaw('reception_status, count(*) as total')
            ->groupBy('reception_status')
            ->pluck('total', 'reception_status');

        $recentContacts = Contact::where('read', false)->latest()->take(6)->get();
        $recentActualites = Actualite::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'stats',
            'dossierStats',
            'chargeParService',
            'dossierParStatut',
            'tauxRefus',
            'recentContacts',
            'recentActualites'
        ));
    }
}
