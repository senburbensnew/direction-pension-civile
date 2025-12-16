@extends('layouts.main')

@section('content')
@if($role === 'ministre')
    <div class="flex flex-col md:flex-row items-center md:items-start gap-8 md:gap-12 lg:gap-16 p-6 md:p-8">
        <!-- Director's Image -->
        <div>
            <x-presentation role="Le Ministre" nom="Alfred Fils METELLUS" sexe="M"
                lien-profil="{{ route('quisommesnous.profil', ['role' => 'ministre']) }}"
                lien-discours="{{ route('quisommesnous.mots', ['role' => 'ministre']) }}"
                mobile-image="images/photo-metelus.png" desktop-image="images/photo-metelus.png" :showProfileLink="true"
                :showSpeechLink="false" />
        </div>

        <!-- Content Card -->
        <div class="w-full md:w-2/3 bg-white rounded-xl shadow-xs transition-shadow p-8 md:p-12">
            <div class="mb-8 border-b-2 border-primary-100 pb-6">
                <h2 class="text-4xl font-bold text-gray-900 mb-4 font-serif tracking-tight">
                    Mots du Ministre
                </h2>
                <p class="text-lg text-gray-600 italic">
                    "La Pension Civile est le reflet de la gratitude de la Nation envers ceux qui l’ont servi avec
                    honneur."
                </p>
            </div>
            <div class="prose-lg text-gray-700 space-y-6">
                <p class="leading-relaxed">
                    Chers collaborateurs, partenaires et citoyens,
                </p>

                <p class="leading-relaxed">
                    La Direction de la Pension Civile occupe une place fondamentale au sein de notre administration.
                    Elle incarne la reconnaissance, par l’État, des années de service loyalement consacrées par nos
                    fonctionnaires au développement national. C’est pourquoi nous avons le devoir d’assurer une gestion
                    transparente, rigoureuse et humaine des pensions publiques.
                </p>

                <div class="pl-6 border-l-4 border-primary-200">
                    <p class="text-gray-600 italic font-medium">
                        "Honorer ceux qui ont servi la nation, c’est affirmer notre engagement envers la justice sociale
                        et la continuité républicaine."
                    </p>
                </div>

                <p class="leading-relaxed">
                    Dans un contexte où la modernisation de l’État est indispensable, notre ministère poursuit des
                    efforts
                    soutenus pour renforcer l’efficacité administrative. Nous travaillons à simplifier les procédures,
                    réduire les délais de traitement et mettre à la disposition des usagers des outils modernisés,
                    accessibles et fiables.
                </p>

                <div class="bg-primary-50 p-6 rounded-lg">
                    <p class="text-primary-800 font-semibold">
                        Priorités 2024 :
                    </p>
                    <ul class="list-disc pl-6 mt-2 space-y-2">
                        <li>Modernisation des systèmes numériques de gestion des pensions</li>
                        <li>Amélioration des mécanismes de traitement et de vérification des dossiers</li>
                        <li>Renforcement de l’assistance et de l’information offertes aux bénéficiaires</li>
                    </ul>
                </div>

                <p class="leading-relaxed">
                    Je tiens à saluer le travail remarquable des équipes du ministère et de la Direction de la Pension
                    Civile. Ensemble, nous poursuivrons la construction d’un système de retraite plus juste, plus
                    efficace
                    et plus proche des citoyens. Votre collaboration, votre professionnalisme et votre sens du devoir
                    demeurent essentiels à l’accomplissement de cette mission nationale.
                </p>

                <div class="mt-8 pt-6 border-t-2 border-primary-100">
                    <p class="text-lg text-gray-700">Avec mes salutations respectueuses,</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">Alfred Fils METELLUS</p>
                    <p class="text-gray-600 font-medium mt-1">
                        Ministre<br>
                        <span class="text-sm text-gray-500">Ministère de l’Économie et des Finances</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
@elseif($role === 'directeur-general')
    <div class="flex flex-col md:flex-row items-center md:items-start gap-8 md:gap-12 lg:gap-16 p-6 md:p-8">
        <!-- Director's Image -->
        <div>
            <x-presentation role="Directeur General" nom="Jean Bouco JEAN JACQUES" sexe="M"
                lien-profil="{{ route('quisommesnous.profil', ['role' => 'directeur-general']) }}"
                lien-discours="{{ route('quisommesnous.mots', ['role' => 'directeur-general']) }}"
                mobile-image="images/directrice-landscapejjj.jpg" desktop-image="images/directricehhh.jpg"
                :showProfileLink="true" :showSpeechLink="false" />
        </div>

        <!-- Content Card -->
        <div class="w-full md:w-2/3 bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow p-8 md:p-12">
            <div class="mb-8 border-b-2 border-primary-100 pb-6">
                <h2 class="text-4xl font-bold text-gray-900 mb-4 font-serif tracking-tight">
                    Mots du Directeur Général
                </h2>
                <p class="text-lg text-gray-600 italic">
                    "Une administration forte repose sur la rigueur, la transparence et la vision."
                </p>
            </div>
            <div class="prose-lg text-gray-700 space-y-6">
                <p class="leading-relaxed">
                    Chers collaborateurs, chers partenaires institutionnels,
                </p>

                <p class="leading-relaxed">
                    Au sein du Ministère de l’Économie et des Finances, nous avons la responsabilité stratégique
                    d’assurer la stabilité et la pérennité du système de pension civile, afin de garantir
                    aux fonctionnaires et retraités une sécurité financière digne et fiable. Cette mission
                    exige rigueur, anticipation et engagement envers la modernisation de nos services.
                </p>

                <div class="pl-6 border-l-4 border-primary-200">
                    <p class="text-gray-600 italic font-medium">
                        "Assurer la retraite de nos agents, c’est honorer le service de toute une vie."
                    </p>
                </div>

                <p class="leading-relaxed">
                    Sous la direction éclairée du Ministre, nous poursuivons la mise en œuvre de réformes
                    indispensables pour renforcer la gestion des pensions civiles, améliorer la transparence
                    des opérations et garantir un versement régulier et sécurisé des droits des bénéficiaires.
                    Nos priorités incluent la modernisation des systèmes de gestion, la simplification des procédures
                    et la protection durable des droits des retraités.
                </p>

                <div class="bg-primary-50 p-6 rounded-lg">
                    <p class="text-primary-800 font-semibold">
                        Priorités stratégiques 2025 :
                    </p>
                    <ul class="list-disc pl-6 mt-2 space-y-2">
                        <li>Renforcement de la gestion financière et du suivi des pensions civiles</li>
                        <li>Digitalisation et sécurisation des systèmes de versement</li>
                        <li>Simplification des démarches pour les bénéficiaires et les institutions partenaires</li>
                        <li>Coordination avec les partenaires nationaux et internationaux pour garantir la stabilité du
                            système</li>
                    </ul>
                </div>

                <p class="leading-relaxed">
                    Je salue l’engagement et le professionnalisme des équipes du ministère, qui œuvrent quotidiennement
                    à assurer le bien-être de nos fonctionnaires retraités et la fiabilité de notre système de pension.
                    Ensemble, nous continuerons à bâtir un ministère moderne et efficace, garantissant à chaque
                    agent le respect et la sécurité de ses droits.
                </p>

                <div class="mt-8 pt-6 border-t-2 border-primary-100">
                    <p class="text-lg text-gray-700">Respectueusement,</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">Jean Bouco JEAN JACQUES</p>
                    <p class="text-gray-600 font-medium mt-1">
                        Directeur Général<br>
                        <span class="text-sm text-gray-500">Ministère de l’Économie et des Finances</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="flex flex-col md:flex-row items-center md:items-start gap-8 md:gap-12 lg:gap-16 p-6 md:p-8">
        <!-- Director's Image -->
        <div>
            <x-presentation role="Directrice" nom="Esther Musac JEUDY" sexe="F"
                lien-profil="{{ route('quisommesnous.profil', ['role' => 'directeur']) }}"
                lien-discours="{{ route('quisommesnous.mots', ['role' => 'directeur']) }}"
                mobile-image="images/directrice-landscape.jpg" desktop-image="images/directrice.jpg"
                :showProfileLink="true" :showSpeechLink="false" />
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
                    La Direction de la Pension Civile joue un rôle essentiel dans la reconnaissance des services rendus
                    par
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
                    Grâce à nos efforts conjugués, nous continuerons à bâtir un système de pension plus juste, plus
                    efficace
                    et plus humain. Votre confiance et votre collaboration sont essentielles à la réussite de cette
                    mission.
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
@endif
@endsection
