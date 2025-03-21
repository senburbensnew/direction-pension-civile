@extends('layouts.main')

@section('content')
    <div class="flex flex-col md:flex-row items-center md:items-start gap-8 md:gap-12 lg:gap-16 p-6 md:p-8">
        <!-- Director's Image -->
        <div>
            <x-presentation />
        </div>

        <!-- Content Card -->
        <div class="w-full md:w-2/3 bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow p-8 md:p-12">
            <div class="mb-8 border-b-2 border-primary-100 pb-6">
                <h2 class="text-4xl font-bold text-gray-900 mb-4 font-serif tracking-tight">
                    Mots de la Directrice
                </h2>
                <p class="text-lg text-gray-600 italic">
                    "Servir avec dévouement, c'est assurer l'avenir de ceux qui ont bâti notre nation."
                </p>
            </div>

            <div class="prose-lg text-gray-700 space-y-6">
                <p class="leading-relaxed">
                    Chers collègues, partenaires et bénéficiaires,
                </p>

                <p class="leading-relaxed">
                    La Direction de la Pension Civile joue un rôle essentiel dans la reconnaissance des services rendus par
                    nos fonctionnaires. Chaque jour, nous œuvrons pour garantir une gestion efficace, transparente et
                    équitable des pensions, afin d'assurer la sérénité de nos retraités et de leurs ayants droit.
                </p>

                <div class="pl-6 border-l-4 border-primary-200">
                    <p class="text-gray-600 italic font-medium">
                        "Un système de retraite juste et efficace est le reflet du respect d'une nation envers ses
                        serviteurs."
                    </p>
                </div>

                <p class="leading-relaxed">
                    Notre engagement est d'améliorer continuellement nos processus, en modernisant nos outils et en
                    simplifiant les démarches pour offrir un service rapide et accessible à tous. Nous mettons un point
                    d'honneur à accompagner chaque bénéficiaire avec professionnalisme et empathie.
                </p>

                <div class="bg-primary-50 p-6 rounded-lg">
                    <p class="text-primary-800 font-semibold">
                        Priorités 2024 :
                    </p>
                    <ul class="list-disc pl-6 mt-2 space-y-2">
                        <li>Digitalisation des services pour une gestion plus fluide</li>
                        <li>Réduction des délais de traitement des dossiers</li>
                        <li>Renforcement de la communication avec les bénéficiaires</li>
                    </ul>
                </div>

                <p class="leading-relaxed">
                    Grâce à nos efforts conjugués, nous continuerons à bâtir un système de pension plus juste, plus efficace
                    et plus humain. Votre confiance et votre collaboration sont essentielles à la réussite de cette mission.
                </p>

                <div class="mt-8 pt-6 border-t-2 border-primary-100">
                    <p class="text-lg text-gray-700">Avec mes salutations respectueuses,</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">Esther Musac

                        JEUDY</p>
                    <p class="text-gray-600 font-medium mt-1">
                        Directrice Générale<br>
                        <span class="text-sm text-gray-500">Direction de la Pension Civile</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
