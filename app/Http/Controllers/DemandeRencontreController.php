<?php

namespace App\Http\Controllers;

use App\Enums\TypeDemandeEnum;
use App\Helpers\CodeGeneratorService;
use App\Models\Demande;
use App\Models\Service;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemandeRencontreController extends Controller
{
    public function create()
    {
        return view('demandes.rencontre.create');
    }

    public function index()
    {
        $demandes = Demande::where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->with('currentStep')
            ->latest()
            ->paginate(20);

        return view('admin.rencontres.index', compact('demandes'));
    }

    public function accepter(Request $request, Demande $demande)
    {
        $step = WorkflowStep::forCode('APPROUVEE');
        abort_unless($step, 500, 'Étape APPROUVEE introuvable.');

        $demande->update(['current_step_id' => $step->id, 'annotation' => $request->input('note')]);

        return redirect()->route('admin.rencontres.index')->with('success', "Rencontre {$demande->code} acceptée.");
    }

    public function refuser(Request $request, Demande $demande)
    {
        $request->validate(['motif' => 'required|string|max:500']);

        $step = WorkflowStep::forCode('REJETEE');
        abort_unless($step, 500, 'Étape REJETEE introuvable.');

        $demande->update(['current_step_id' => $step->id, 'annotation' => $request->motif]);

        return redirect()->route('admin.rencontres.index')->with('success', "Rencontre {$demande->code} refusée.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prenom'          => 'required|string|max:100',
            'nom'             => 'required|string|max:100',
            'email'           => 'required|email|max:255',
            'telephone'       => 'nullable|string|max:30',
            'organisation'    => 'nullable|string|max:200',
            'objet'           => 'required|string|max:300',
            'date_souhaitee'  => 'required|date|after_or_equal:today',
            'heure_souhaitee' => 'required|string|max:10',
            'plateforme'      => 'required|in:zoom,teams,meet,autre',
            'message'         => 'nullable|string|max:2000',
        ]);

        $demande = DB::transaction(function () use ($validated) {
            $serviceId = Service::where('code', Service::DIRECTION)->value('id');
            $soumiseId = WorkflowStep::idForCode('SOUMISE');

            return Demande::create([
                'code'               => CodeGeneratorService::generateUniqueRequestCode(
                    TypeDemandeEnum::DEMANDE_RENCONTRE->value,
                    (new Demande())->getTable()
                ),
                'type'               => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
                'created_by'         => auth()->id(),
                'current_step_id'    => $soumiseId,
                'current_service_id' => $serviceId,
                'submitted_at'       => now(),
                'is_urgent'          => false,
                'data'               => $validated,
            ]);
        });

        return redirect()->route('demandes.rencontre.create')
            ->with('success', "Votre demande de visioconférence a été envoyée avec succès. Référence : {$demande->code}");
    }
}
