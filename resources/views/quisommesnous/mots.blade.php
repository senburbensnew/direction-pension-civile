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
                    "Le leadership, c'est l'art de donner aux gens une plateforme pour que les choses se passent"
                </p>
            </div>

            <div class="prose-lg text-gray-700 space-y-6">
                <p class="leading-relaxed">
                    Chers collègues, partenaires et citoyens,
                </p>

                <p class="leading-relaxed">
                    C'est avec une grande joie et un profond engagement que je m'adresse à vous aujourd'hui. Notre mission
                    au sein de cette institution est de servir avec intégrité et efficacité, en mettant toujours les besoins
                    de nos citoyens et partenaires au cœur de nos actions.
                </p>

                <div class="pl-6 border-l-4 border-primary-200">
                    <p class="text-gray-600 italic font-medium">
                        "Le véritable service commence là où l'effort personnel rencontre le besoin collectif."
                    </p>
                </div>

                <p class="leading-relaxed">
                    Nous avons fait de nombreux progrès, mais il reste encore beaucoup à faire pour garantir que chacun de
                    vous puisse bénéficier des services que nous offrons, dans les meilleures conditions possibles. Notre
                    équipe travaille sans relâche pour renforcer nos structures, améliorer nos processus et offrir une
                    expérience de qualité à toutes les personnes avec lesquelles nous interagissons.
                </p>

                <div class="bg-primary-50 p-6 rounded-lg">
                    <p class="text-primary-800 font-semibold">
                        Objectifs 2024 :
                    </p>
                    <ul class="list-disc pl-6 mt-2 space-y-2">
                        <li>Modernisation des infrastructures</li>
                        <li>Formation continue des équipes</li>
                        <li>Amélioration de l'expérience utilisateur</li>
                    </ul>
                </div>

                <p class="leading-relaxed">
                    Ensemble, nous avons la capacité de surmonter les défis et de construire un avenir plus prospère pour
                    notre communauté. Merci de votre confiance et de votre engagement continu.
                </p>

                <div class="mt-8 pt-6 border-t-2 border-primary-100">
                    <p class="text-lg text-gray-700">Avec mes salutations respectueuses,</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">Marie Dupont</p>
                    <p class="text-gray-600 font-medium mt-1">
                        Directrice Générale<br>
                        <span class="text-sm text-gray-500">Institut National de l'Excellence</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
