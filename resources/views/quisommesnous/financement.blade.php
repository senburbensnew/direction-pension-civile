@extends('layouts.main')

@section('content')
    <div class="container mx-auto p-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Financement de la Pension Civile</h1>

            <div class="border-l-4 border-blue-500 pl-4 mb-6">
                <p class="text-lg text-gray-700">
                    Le financement de la Direction de la Pension Civile repose sur plusieurs sources, garantissant la
                    pérennité et l’équilibre du système de retraite des fonctionnaires.
                </p>
            </div>

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-3">Sources de Financement</h2>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
                <li><span class="font-semibold">Cotisations des fonctionnaires :</span> Prélèvements obligatoires sur les
                    salaires des agents publics.</li>
                <li><span class="font-semibold">Contributions de l’État :</span> Subventions directes du gouvernement pour
                    garantir le versement des pensions.</li>
                <li><span class="font-semibold">Revenus d’investissement :</span> Placements financiers réalisés pour
                    optimiser la gestion des fonds de pension.</li>
                <li><span class="font-semibold">Autres sources :</span> Contributions spéciales et financements
                    exceptionnels en cas de déséquilibre budgétaire.</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-3">Répartition Budgétaire</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-200 p-4 rounded-lg shadow-md">
                    <p class="font-semibold text-gray-700">Paiement des pensions</p>
                    <p class="text-sm text-gray-600">Assure le versement régulier aux retraités.</p>
                </div>
                <div class="bg-gray-200 p-4 rounded-lg shadow-md">
                    <p class="font-semibold text-gray-700">Gestion administrative</p>
                    <p class="text-sm text-gray-600">Frais de fonctionnement et gestion du personnel.</p>
                </div>
                <div class="bg-gray-200 p-4 rounded-lg shadow-md">
                    <p class="font-semibold text-gray-700">Fonds de réserve</p>
                    <p class="text-sm text-gray-600">Provision pour garantir la stabilité du système.</p>
                </div>
                <div class="bg-gray-200 p-4 rounded-lg shadow-md">
                    <p class="font-semibold text-gray-700">Modernisation</p>
                    <p class="text-sm text-gray-600">Investissements dans la digitalisation et l'amélioration des services.
                    </p>
                </div>
            </div>

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-3">Transparence et Contrôle</h2>
            <p class="text-gray-600">
                La Direction de la Pension Civile assure une gestion transparente de ses ressources financières, avec des
                audits réguliers et des rapports publiés annuellement.
            </p>
        </div>
    </div>
@endsection
