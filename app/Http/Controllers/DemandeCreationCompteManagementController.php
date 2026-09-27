<?php

namespace App\Http\Controllers;

use App\Models\DemandeCreationCompte;
use App\Services\AccountCreation\DemandeCreationCompteReviewService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DemandeCreationCompteManagementController extends Controller
{
    public function __construct(private DemandeCreationCompteReviewService $review)
    {
    }

    public function index(Request $request)
    {
        $filtre = $request->string('filtre')->toString() ?: 'en_attente';

        $query = DemandeCreationCompte::query()->latest();

        if ($filtre === 'en_attente') {
            $query->where('status', DemandeCreationCompte::STATUS_EN_ATTENTE);
        } elseif ($filtre === 'acceptees') {
            $query->where('status', DemandeCreationCompte::STATUS_ACCEPTEE);
        } elseif ($filtre === 'refusees') {
            $query->where('status', DemandeCreationCompte::STATUS_REFUSEE);
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', $term)
                    ->orWhere('nif', 'like', $term)
                    ->orWhere('pension_code', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('telephone', 'like', $term)
                    ->orWhere('firstname', 'like', $term)
                    ->orWhere('lastname', 'like', $term);
            });
        }

        $demandes = $query->paginate(20)->withQueryString();

        return view($this->viewName('index'), [
            'demandes' => $demandes,
            'filtre' => $filtre,
            'routePrefix' => $this->routePrefix(),
            'canDecide' => $this->canDecide(),
        ]);
    }

    public function show(DemandeCreationCompte $demandeCreationCompte)
    {
        $demandeCreationCompte->load(['user', 'reviewer', 'histories.actor']);

        return view($this->viewName('show'), [
            'demande' => $demandeCreationCompte,
            'routePrefix' => $this->routePrefix(),
            'canDecide' => $this->canDecide(),
        ]);
    }

    public function accepter(Request $request, DemandeCreationCompte $demandeCreationCompte)
    {
        abort_unless($this->canDecide(), 403);

        $validated = $request->validate([
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:8|max:72',
        ]);

        try {
            $result = $this->review->accept($demandeCreationCompte, $request->user(), $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $user = $result['user'];

        return redirect()
            ->route($this->named('show'), $demandeCreationCompte)
            ->with('success', "Demande acceptée. Compte de {$user->displayName()} ({$user->email}).");
    }

    public function refuser(Request $request, DemandeCreationCompte $demandeCreationCompte)
    {
        abort_unless($this->canDecide(), 403);

        $validated = $request->validate([
            'refusal_reason' => 'required|string|min:5|max:1000',
        ]);

        try {
            $this->review->refuse($demandeCreationCompte, $request->user(), $validated['refusal_reason']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route($this->named('show'), $demandeCreationCompte)
            ->with('success', 'Demande refusée. Le compte et les informations fournies ont été supprimés.');
    }

    private function routePrefix(): string
    {
        return $this->usesAdminUi() ? 'admin' : 'formalites';
    }

    private function named(string $action): string
    {
        return $this->routePrefix().'.comptes-demandes.'.$action;
    }

    private function viewName(string $page): string
    {
        return $this->usesAdminUi()
            ? 'admin.comptes-demandes.'.$page
            : 'demandes.comptes-demandes.'.$page;
    }

    private function usesAdminUi(): bool
    {
        return request()->routeIs('admin.*');
    }

    private function canDecide(): bool
    {
        return ! $this->usesAdminUi()
            && (bool) request()->user()?->hasRole(\App\Models\User::ROLE_AGENT_FORMALITES);
    }
}
