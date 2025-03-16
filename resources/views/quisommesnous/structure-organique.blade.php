@extends('layouts.main')

@section('content')
    <div class="container mx-auto p-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Structure Organique</h1>

            <div class="border-l-4 border-blue-500 pl-4 mb-6">
                <p class="text-lg text-gray-700">
                    La Direction de la Pension Civile est organisée en plusieurs départements et services spécialisés afin
                    d’assurer une gestion efficace des pensions civiles.
                </p>
            </div>

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-3">Organigramme</h2>
            <div class="flex flex-col items-center">
                <div class="bg-blue-500 text-white px-6 py-3 rounded-lg shadow-md mb-4">
                    <p class="font-bold">Directeur Général</p>
                </div>

                <div class="flex flex-wrap justify-center gap-6">
                    <div class="bg-gray-200 p-4 rounded-lg shadow-md w-64 text-center">
                        <p class="font-semibold text-gray-700">Service des Études et Réformes</p>
                        <p class="text-sm text-gray-600">Analyse et amélioration du système des pensions.</p>
                    </div>
                    <div class="bg-gray-200 p-4 rounded-lg shadow-md w-64 text-center">
                        <p class="font-semibold text-gray-700">Service de Gestion des Dossiers</p>
                        <p class="text-sm text-gray-600">Traitement et suivi des demandes de pension.</p>
                    </div>
                    <div class="bg-gray-200 p-4 rounded-lg shadow-md w-64 text-center">
                        <p class="font-semibold text-gray-700">Service de Contrôle et Audit</p>
                        <p class="text-sm text-gray-600">Vérification et contrôle des paiements.</p>
                    </div>
                    <div class="bg-gray-200 p-4 rounded-lg shadow-md w-64 text-center">
                        <p class="font-semibold text-gray-700">Service Informatique</p>
                        <p class="text-sm text-gray-600">Gestion des bases de données et numérisation.</p>
                    </div>
                </div>
            </div>

            <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-3">Coordination</h2>
            <p class="text-gray-600">
                Chaque service fonctionne sous la supervision du Directeur Général et collabore avec les autres départements
                pour garantir une gestion efficace des pensions civiles.
            </p>
        </div>
    </div>
@endsection
