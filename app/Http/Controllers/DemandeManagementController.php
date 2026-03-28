<?php


namespace App\Http\Controllers;


use App\Models\Demande;
use App\Models\DemandeActivityLog;
use App\Models\DemandeHistory;
use App\Models\DemandeMessage;
use App\Models\Service;
use App\Models\Status;
use App\Notifications\DemandeStatusChangedNotification;
use App\Notifications\DemandeTransferredNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransfertDemandeRequest;

class DemandeManagementController extends Controller
{
    public function index()
    {
        $demandes = Demande::orderBy('created_at', 'desc')->paginate(50);
        return view('demandes.admin.index', compact('demandes'));
    }

    public function edit(Demande $demande)
    {
        return view('demandes.admin.edit', compact('demande'));
    }

    public function updateStatus(Request $request, Demande $demande)
    {
        $request->validate([
            'etat'        => 'required|string',
            'commentaire' => 'nullable|string',
        ]);

        DB::transaction(function () use ($demande, $request) {
            $demande->update(['etat' => $request->etat]);
            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => $request->etat,
                'commentaire' => $request->commentaire,
                'changed_by'  => auth()->id(),
            ]);
        });

        // Notify the demande owner
        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification(
                    $demande,
                    $request->etat,
                    $request->commentaire,
                ));
            }
        } catch (\Throwable $e) {
            Log::error('updateStatus: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'État mis à jour');
    }

    /**
     * Annotation du dossier par la Direction.
     * Obligatoire avant tout transfert, impression ou téléchargement.
     */
    public function annotate(Request $request, Demande $demande)
    {
        abort_unless(auth()->user()->hasRole('direction'), 403, 'Seule la Direction peut annoter un dossier.');

        $request->validate([
            'annotation' => 'required|string|max:2000',
            'folder'     => 'nullable|in:pension,urgent,suivi,correspondances,rencontre',
        ]);

        DB::transaction(function () use ($demande, $request) {
            $demande->update([
                'annotation'   => $request->annotation,
                'annotated_by' => auth()->id(),
                'annotated_at' => now(),
                'folder'       => $request->folder,
            ]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => $demande->status->code,
                'commentaire' => 'Annotation par la Direction : ' . $request->annotation,
                'changed_by'  => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'Dossier annoté avec succès.');
    }

    /**
     * Demande de complément d'information à l'usager.
     * Le dossier passe en statut COMPLEMENT_REQUIS et un message est envoyé.
     */
    public function requestComplement(Request $request, Demande $demande)
    {
        $request->validate([
            'message' => 'required|string|max:3000',
        ]);

        $previousStatusCode = $demande->status->code;

        DB::transaction(function () use ($demande, $request, $previousStatusCode) {
            $complementStatusId = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');

            $demande->update(['status_id' => $complementStatusId]);

            DemandeMessage::create([
                'demande_id' => $demande->id,
                'sender_id'  => auth()->id(),
                'body'       => $request->message,
            ]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => 'COMPLEMENT_REQUIS',
                'commentaire' => 'Complément requis : ' . $request->message,
                'changed_by'  => auth()->id(),
            ]);
        });

        // Notify the demande owner (the observer will also fire, but being explicit here
        // ensures the message is included even if the observer skips it)
        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification(
                    $demande,
                    'COMPLEMENT_REQUIS',
                    $request->message,
                ));
            }
        } catch (\Throwable $e) {
            Log::error('requestComplement: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Demande de complément envoyée à l\'usager.');
    }

    /**
     * Transfert d'un dossier vers un service.
     * Bloqué si la Direction n'a pas encore annoté le dossier.
     */
    public function transfererDemande(TransfertDemandeRequest $request)
    {
        $demande = Demande::with('status')->findOrFail($request->demande_id);

        abort_if(
            !$demande->isAnnotated(),
            403,
            'Le dossier doit être annoté par la Direction avant d\'être transféré.'
        );

        $toService = Service::findOrFail($request->service_id);

        // La Direction ne peut transférer qu'au Secrétariat
        if (auth()->user()->hasRole('direction')) {
            abort_unless(
                $toService->code === Service::SECRETARIAT,
                403,
                'La Direction ne peut transférer qu\'au Secrétariat.'
            );
        }

        DB::transaction(function () use ($demande, $toService, $request) {
            $fromServiceId = $demande->current_service_id;

            $demande->update([
                'current_service_id' => $toService->id,
            ]);

            $demande->workflows()->create([
                'from_service_id'   => $fromServiceId,
                'to_service_id'     => $toService->id,
                'status_id'         => $demande->status_id,
                'action_by_user_id' => auth()->id(),
                'commentaire'       => $request->commentaire,
            ]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => $demande->status->code,
                'commentaire' => 'Dossier transféré vers : ' . $toService->nom . ($request->commentaire ? ' — ' . $request->commentaire : ''),
                'changed_by'  => auth()->id(),
            ]);

            DemandeActivityLog::create([
                'demande_id' => $demande->id,
                'user_id'    => auth()->id(),
                'action'     => 'transferred',
                'metadata'   => [
                    'to_service'   => $toService->nom,
                    'commentaire'  => $request->commentaire,
                ],
            ]);
        });

        // Notify all users assigned to the target service
        try {
            $serviceUsers = $toService->users;
            foreach ($serviceUsers as $user) {
                $user->notify(new DemandeTransferredNotification($demande, $toService, $request->commentaire));
            }
        } catch (\Throwable $e) {
            Log::error('transfererDemande: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('personal.cart')->with('success', 'Dossier transféré avec succès vers ' . $toService->nom . '.');
    }
}
