<?php

namespace App\Http\Controllers;

use App\Http\Requests\DemandeCreationCompte\AcceptDemandeCreationCompteRequest;
use App\Http\Requests\DemandeCreationCompte\RefuseDemandeCreationCompteRequest;
use App\Models\DemandeCreationCompte;
use App\Models\User;
use App\Services\AccountCreation\DemandeCreationCompteReviewService;
use App\Support\Storage\InteractsWithFilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class DemandeCreationCompteManagementController extends Controller
{
    use InteractsWithFilesystemAdapter;

    public function __construct(private DemandeCreationCompteReviewService $review) {}

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

        $demandes = $query->paginate(20);
        if ($demandes instanceof AbstractPaginator) {
            $demandes->withQueryString();
        }

        return view($this->viewName('index'), [
            'demandes' => $demandes,
            'filtre' => $filtre,
            'routePrefix' => $this->routePrefix(),
            'canDecide' => $this->canDecide(),
        ]);
    }

    public function show(DemandeCreationCompte $demandeCreationCompte)
    {
        $demandeCreationCompte->load(['user', 'traiteePar', 'histories.actor']);

        return view($this->viewName('show'), [
            'demande' => $demandeCreationCompte,
            'rendezVousPourDecisionCompte' => $demandeCreationCompte->rendezVousPourDecisionCompte(),
            'routePrefix' => $this->routePrefix(),
            'canDecide' => $this->canDecide(),
        ]);
    }

    public function piece(
        Request $request,
        DemandeCreationCompte $demandeCreationCompte,
        Media $media
    ): Response {
        abort_unless($this->canViewPieces(), 403);

        abort_unless(
            $media->model_type === DemandeCreationCompte::class
            && (int) $media->model_id === (int) $demandeCreationCompte->id,
            404
        );

        $relativePath = $media->getPathRelativeToRoot();
        $disk = $this->filesystemAdapter($media->disk);

        abort_unless($disk->exists($relativePath), 404);

        $disposition = $request->boolean('inline') ? 'inline' : 'attachment';
        $filename = str_replace('"', '', $media->file_name);

        return response()->file(
            $disk->path($relativePath),
            [
                'Content-Type' => $media->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            ]
        );
    }

    public function accepter(AcceptDemandeCreationCompteRequest $request, DemandeCreationCompte $demandeCreationCompte)
    {
        abort_unless($this->canDecide(), 403);

        $validated = $request->validated();

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

    public function refuser(RefuseDemandeCreationCompteRequest $request, DemandeCreationCompte $demandeCreationCompte)
    {
        abort_unless($this->canDecide(), 403);

        $validated = $request->validated();

        try {
            $this->review->refuse($demandeCreationCompte, $request->user(), $validated['refusal_reason']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route($this->named('show'), $demandeCreationCompte)
            ->with('success', 'Demande refusée. Le compte provisoire a été bloqué.');
    }

    private function routePrefix(): string
    {
        return 'formalites';
    }

    private function named(string $action): string
    {
        return $this->routePrefix().'.comptes-demandes.'.$action;
    }

    private function viewName(string $page): string
    {
        return 'demandes.comptes-demandes.'.$page;
    }

    private function canDecide(): bool
    {
        return (bool) request()->user()?->hasRole(User::ROLE_AGENT_FORMALITES);
    }

    private function canViewPieces(): bool
    {
        return (bool) request()->user()?->hasRole(User::ROLE_AGENT_FORMALITES);
    }
}
