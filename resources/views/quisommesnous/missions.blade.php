@extends('layouts.main')

@section('content')
    <div class="container mx-auto p-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Missions et Attributions</h1>

            <div class="border-l-4 border-blue-500 pl-4 mb-6">
                <p class="text-lg text-gray-700">
                    La Direction de la Pension Civile est chargée de la gestion et du suivi des pensions civiles. Elle
                    veille à l'application des réglementations en vigueur et assure le paiement des droits des retraités de
                    la fonction publique.
                </p>
            </div>

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-3">Missions Principales</h2>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
                <li>Traitement et liquidation des dossiers de pension.</li>
                <li>Élaboration et suivi des réformes en matière de pension civile.</li>
                <li>Gestion des paiements et contrôle des bénéficiaires.</li>
                <li>Conseil et assistance aux fonctionnaires et retraités.</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-3">Attributions</h2>
            <p class="text-gray-600">
                La Direction est également responsable de la mise en place de mesures visant à améliorer l'efficacité du
                système de pension civile, en collaboration avec les différentes instances gouvernementales.
            </p>
        </div>
    </div>
@endsection
