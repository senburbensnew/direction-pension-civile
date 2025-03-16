@extends('layouts.main')

@section('content')
    <div class="container mx-auto p-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Historique de la Direction de la Pension Civile</h1>

            <div class="border-l-4 border-blue-500 pl-4 mb-6">
                <p class="text-lg text-gray-700">
                    La Direction de la Pension Civile a été créée pour assurer la gestion efficace des pensions des
                    fonctionnaires civils. Depuis sa création, elle a évolué en fonction des réformes administratives et
                    économiques.
                </p>
            </div>

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-3">Origines et Création</h2>
            <p class="text-gray-600">
                Fondée dans les années [année de création], cette direction avait pour but initial d’organiser le paiement
                des pensions aux fonctionnaires retraités. Au fil du temps, elle s'est modernisée pour répondre aux nouveaux
                défis de l’administration publique.
            </p>

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-3">Évolutions et Réformes</h2>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
                <li>Introduction des premiers systèmes de gestion automatisée.</li>
                <li>Réformes majeures en [année] pour améliorer l'efficacité du traitement des dossiers.</li>
                <li>Intégration de nouvelles technologies pour une meilleure transparence.</li>
                <li>Collaboration avec d'autres institutions pour assurer une meilleure prise en charge des retraités.</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-3">Mission Aujourd’hui</h2>
            <p class="text-gray-600">
                Aujourd'hui, la Direction de la Pension Civile continue d'améliorer ses services en intégrant des outils
                numériques modernes et en optimisant les procédures administratives pour une meilleure gestion des pensions.
            </p>
        </div>
    </div>
@endsection
