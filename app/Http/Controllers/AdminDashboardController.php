<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Contact;
use App\Models\Demande;
use App\Models\DemandeInteraction;
use App\Models\Newsletter;
use App\Models\Report;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
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
        $dossierStats = [
            'total'             => Demande::count(),
            'en_cours'          => Demande::active()->count(),
            'urgents'           => Demande::active()->where('is_urgent', true)->count(),
            'delai_legal'       => Demande::active()
                                    ->whereNotNull('submitted_at')
                                    ->where('submitted_at', '<=', now()->subDays(30))
                                    ->count(),
            'en_attente_recep'  => Demande::whereHas('currentStep', fn($q) => $q->where('code', 'TRANSFERT_EN_ATTENTE'))->count(),
        ];

        // Charge par service (dossiers actifs par service courant)
        $chargeParService = Service::withCount([
            'demandes as dossiers_actifs' => fn($q) => $q->active(),
        ])->orderByDesc('dossiers_actifs')->get();

        // Dossiers par étape de workflow
        $dossierParStatut = WorkflowStep::withCount('demandes')
            ->orderByDesc('demandes_count')
            ->get()
            ->filter(fn($s) => $s->demandes_count > 0);

        // Dossiers par type de demande
        $dossierParType = Demande::selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'type'  => $r->type,
                'label' => \App\Enums\TypeDemandeEnum::tryFrom($r->type)?->label() ?? $r->type,
                'total' => $r->total,
            ]);

        // Taux de traitement des transferts (30 derniers jours)
        $tauxRefus = DemandeInteraction::where('type', DemandeInteraction::TYPE_TRANSFERT)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('statut, count(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $recentContacts = Contact::where('read', false)->latest()->take(6)->get();
        $recentActualites = Actualite::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'stats',
            'dossierStats',
            'chargeParService',
            'dossierParStatut',
            'dossierParType',
            'tauxRefus',
            'recentContacts',
            'recentActualites'
        ));
    }
}
