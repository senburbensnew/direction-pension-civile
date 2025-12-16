@extends('layouts.main')

@section('content')
@if($role === 'ministre')
    <section class="py-10 md:py-16 bg-gray-50">
        <div class="container mx-auto px-6 md:px-10 flex flex-col md:flex-row items-start gap-5">
            <!-- Director's Image -->
            <div class="md:w-1/3 lg:w-1/4">
                <x-presentation role="Le Ministre" nom="Alfred Fils METELLUS" sexe="M"
                    lien-profil="{{ route('quisommesnous.profil', ['role' => 'ministre']) }}"
                    lien-discours="{{ route('quisommesnous.mots', ['role' => 'ministre']) }}"
                    mobile-image="images/photo-metelus.png" desktop-image="images/photo-metelus.png"
                    :showProfileLink="false" :showSpeechLink="true" />
            </div>

            <!-- Content Card -->
            <div class="w-full bg-white rounded-2xl shadow-xs border border-gray-200 p-8 md:p-12">

                <!-- Header -->
                <div class="mb-8 pb-6 border-b border-gray-200">
                    <h1 class="text-4xl font-extrabold text-gray-900 font-serif mb-2">
                        Présentation du Ministre
                    </h1>
                    <p class="text-lg text-gray-600">
                        M. Alfred Fils Metellus — Ministère de l'Economie et des Finances
                    </p>
                </div>

                <!-- Biography -->
                <div class="prose prose-lg max-w-none text-gray-700 space-y-6">
                    <!-- Mission Section -->
                    <div class="pt-8 space-y-4">
                        <p>
                            <p>Mercredi 13 novembre 2024, M. Alfred Fils METELLUS a pris officiellement charge de ses fonctions en tant que Ministre de l’Economie et des Finances au cours d’une cérémonie conduite par le Premier Ministre Alix Didier Fils Aime.</p>
                            <p>M. Alfred Fils METELLUS est un économiste haïtien avec plus de 30 ans d'expérience professionnelle dans le domaine de l'analyse des politiques macroéconomiques, du développement, de la gestion des finances publiques, de la réforme institutionnelle, de la mise en œuvre et de l'évaluation des projets.</p>
                            <p>M. Alfred Fils METELLUS possède une vaste expérience dans le secteur public haïtien : après avoir obtenu son diplôme en Planification au Centre de Techniques de Planification et d'Economie Appliquée (CTPEA), il a été nommé Economiste – Planificateur à la Direction Départementale du Sud du Ministère de la Planification et de la Coopération Externe (MPCE), puis Analyste de projets chargé des programmes d'investissement public du Ministère de l'Agriculture, des Ressources Naturelles et du Développement Rural (MARNDR M. Metellus est aussi titulaire d'une maîtrise en Gestion des Politiques Economiques du CERDI (Centre d'Etudes et de Recherches sur le Développement International) de l'Université d'Auvergne, France.</p>
                            <p>En novembre 2018, M. Alfred Fils METELLUS a été nommé Conseiller pour Haïti au Bureau Exécutif d'Argentine et d’Haïti à la Banque Interaméricaine de Développement (BID). Il Avant cette nomination, il était Consultant à la BID, de 2014 à 2018, avec pour mission de conseiller les équipes de projet sur le processus de renforcement des institutions publiques telles que le Ministère de l'Éducation Nationale et de la Formation Professionnelle (MENFP), le Fonds d'Assistance Économique et Sociale (FAES) et la Société Nationale des Parcs Industriels (SONAPI) ainsi que de contribuer au dialogue avec les autorités nationales dans les domaines du budget, des finances publiques et de l'investissement public tout en contribuant à la préparation des revues périodiques de portefeuille entre le gouvernement haïtien et la BID.</p>
                            <p>La nomination de M. Alfred Fils METELLUS comme Ministre de l’Economie et des Finances marque son retour au sein de la grande famille du MEF ou, en sa qualité de Directeur des études Economiques, il était responsable d'élaborer le cadre macroéconomique du Budget de la République, de coordonner les négociations avec les institutions financières internationales et de suivre les programmes financiers conclus avec le FMI. Il était également responsable des négociations et de la coordination des missions d'appui budgétaire fournies par des bailleurs de fonds tels que la BID, la Banque mondiale et l'Union Européenne (UE). Ainsi, grâce aux appuis budgétaires fournis par les bailleurs de fonds, diverses réformes dans les domaines des finances publiques, du climat des affaires, de la lutte contre la corruption, de la transparence dans la gestion des finances publiques et de la reddition des comptes ont été mises en œuvre. Il est à noter, cependant, que M. Alfred Fils Metellus également servi le MEF, successivement comme Directeur de Cabinet de plusieurs Ministres et comme Secrétaire d'État à l'Économie, chargé d'élaborer et de mettre en œuvre un ensemble de mesures politiques visant à stimuler l'activité économique et à moderniser ses structures.</p>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>
@elseif($role === 'directeur-general')
    <section class="py-10 md:py-16 bg-gray-50">
        <div class="container mx-auto px-6 md:px-10 flex flex-col md:flex-row items-start gap-5">
            <!-- Director's Image -->
            <div class="md:w-1/3 lg:w-1/4">
                <x-presentation role="Directeur General" nom="Jean Bouco JEAN JACQUES" sexe="M"
                    lien-profil="{{ route('quisommesnous.profil', ['role' => 'directeur-general']) }}"
                    lien-discours="{{ route('quisommesnous.mots', ['role' => 'directeur-general']) }}"
                    mobile-image="images/directrice-landscapejjj.jpg" desktop-image="images/directricehhh.jpg"
                    :showProfileLink="false" :showSpeechLink="true" />
            </div>

            <!-- Content Card -->
            <div class="w-full bg-white rounded-2xl shadow-lg border border-gray-200 p-8 md:p-12">

                <!-- Header -->
                <div class="mb-8 pb-6 border-b border-gray-200">
                    <h1 class="text-4xl font-extrabold text-gray-900 font-serif mb-2">
                        Présentation de la Directrice
                    </h1>
                    <p class="text-lg text-gray-600">
                        Mme Esther Musac JEUDY — Directrice Générale de la Direction de la Pension Civile
                    </p>
                </div>

                <!-- Biography -->
                <div class="prose prose-lg max-w-none text-gray-700 space-y-6">

                    <p>
                        Mme <strong>Esther Musac JEUDY</strong> est une professionnelle reconnue de l’administration
                        publique,
                        dotée d’une solide expérience dans la gestion institutionnelle, la coordination stratégique et
                        la
                        modernisation des services administratifs. Son parcours est marqué par un engagement constant en
                        faveur
                        de l’efficacité, de la transparence et de l’amélioration continue du service public.
                    </p>

                    <p>
                        Avant d’être nommée Directrice Générale de la <strong>Direction de la Pension Civile</strong>,
                        elle a occupé plusieurs fonctions de responsabilité au sein de l’administration, où elle s’est
                        distinguée par sa rigueur, son leadership positif et sa capacité à piloter des réformes
                        structurantes.
                    </p>

                    <!-- Career Section -->
                    <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 shadow-sm">
                        <h2 class="text-2xl font-bold text-blue-800 mb-3">Parcours professionnel</h2>

                        <ul class="list-disc pl-6 space-y-2 text-gray-700">
                            <li>Directrice Générale — Direction de la Pension Civile</li>
                            <li>Responsable administratif et financier dans divers organismes publics</li>
                            <li>Coordinatrice de projets institutionnels de modernisation administrative</li>
                            <li>Formatrice en gestion publique et amélioration des processus</li>
                        </ul>
                    </div>

                    <!-- Skills Section -->
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h2 class="text-2xl font-bold text-gray-900 mb-3">Domaines de compétence</h2>

                        <ul class="list-disc pl-6 space-y-2 text-gray-700">
                            <li>Gestion administrative et pilotage stratégique</li>
                            <li>Réforme institutionnelle et modernisation des services</li>
                            <li>Management d’équipe et leadership collaboratif</li>
                            <li>Amélioration de la qualité et optimisation des processus</li>
                            <li>Communication publique et relation avec les usagers</li>
                        </ul>
                    </div>

                    <!-- Mission Section -->
                    <div class="pt-8 space-y-4">
                        <h2 class="text-2xl font-bold text-gray-900">Vision & engagement</h2>

                        <p>
                            À la tête de la Direction de la Pension Civile, Mme JEUDY œuvre pour renforcer
                            un système de retraite plus efficace, plus transparent et plus proche des usagers.
                            Elle place l'humain au cœur de chaque décision et veille au respect des droits
                            des retraités et de leurs familles.
                        </p>

                        <p>
                            Sa mission s’inscrit dans la volonté de faire de la Pension Civile un modèle
                            de gestion moderne, fiable et accessible à tous.
                        </p>
                    </div>

                    <!-- Signature -->
                    <div class="pt-8 mt-6 border-t border-gray-200">
                        <p class="text-lg font-semibold text-gray-900">Mme Esther Musac JEUDY</p>
                        <p class="text-gray-600 font-medium">
                            Directrice Générale<br>
                            <span class="text-sm text-gray-500">Direction de la Pension Civile</span>
                        </p>
                    </div>

                </div>

            </div>
        </div>
    </section>
@else
    <section class="py-10 md:py-16 bg-gray-50">
        <div class="container mx-auto px-6 md:px-10 flex flex-col md:flex-row items-start gap-5">
            <!-- Director's Image -->
            <div class="md:w-1/3 lg:w-1/4">
                <x-presentation role="Directrice" nom="Esther Musac JEUDY" sexe="F"
                    lien-profil="{{ route('quisommesnous.profil', ['role' => 'directeur']) }}"
                    lien-discours="{{ route('quisommesnous.mots', ['role' => 'directeur']) }}"
                    mobile-image="images/directrice-landscape.jpg" desktop-image="images/directrice.jpg"
                    :showProfileLink="false" :showSpeechLink="true" />
            </div>

            <!-- Content Card -->
            <div class="w-full bg-white rounded-2xl shadow-lg border border-gray-200 p-8 md:p-12">

                <!-- Header -->
                <div class="mb-8 pb-6 border-b border-gray-200">
                    <h1 class="text-4xl font-extrabold text-gray-900 font-serif mb-2">
                        Présentation de la Directrice
                    </h1>
                    <p class="text-lg text-gray-600">
                        Mme Esther Musac JEUDY — Directrice Générale de la Direction de la Pension Civile
                    </p>
                </div>

                <!-- Biography -->
                <div class="prose prose-lg max-w-none text-gray-700 space-y-6">

                    <p>
                        Mme <strong>Esther Musac JEUDY</strong> est une professionnelle reconnue de l’administration
                        publique,
                        dotée d’une solide expérience dans la gestion institutionnelle, la coordination stratégique et
                        la
                        modernisation des services administratifs. Son parcours est marqué par un engagement constant en
                        faveur
                        de l’efficacité, de la transparence et de l’amélioration continue du service public.
                    </p>

                    <p>
                        Avant d’être nommée Directrice Générale de la <strong>Direction de la Pension Civile</strong>,
                        elle a occupé plusieurs fonctions de responsabilité au sein de l’administration, où elle s’est
                        distinguée par sa rigueur, son leadership positif et sa capacité à piloter des réformes
                        structurantes.
                    </p>

                    <!-- Career Section -->
                    <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 shadow-sm">
                        <h2 class="text-2xl font-bold text-blue-800 mb-3">Parcours professionnel</h2>

                        <ul class="list-disc pl-6 space-y-2 text-gray-700">
                            <li>Directrice Générale — Direction de la Pension Civile</li>
                            <li>Responsable administratif et financier dans divers organismes publics</li>
                            <li>Coordinatrice de projets institutionnels de modernisation administrative</li>
                            <li>Formatrice en gestion publique et amélioration des processus</li>
                        </ul>
                    </div>

                    <!-- Skills Section -->
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h2 class="text-2xl font-bold text-gray-900 mb-3">Domaines de compétence</h2>

                        <ul class="list-disc pl-6 space-y-2 text-gray-700">
                            <li>Gestion administrative et pilotage stratégique</li>
                            <li>Réforme institutionnelle et modernisation des services</li>
                            <li>Management d’équipe et leadership collaboratif</li>
                            <li>Amélioration de la qualité et optimisation des processus</li>
                            <li>Communication publique et relation avec les usagers</li>
                        </ul>
                    </div>

                    <!-- Mission Section -->
                    <div class="pt-8 space-y-4">
                        <h2 class="text-2xl font-bold text-gray-900">Vision & engagement</h2>

                        <p>
                            À la tête de la Direction de la Pension Civile, Mme JEUDY œuvre pour renforcer
                            un système de retraite plus efficace, plus transparent et plus proche des usagers.
                            Elle place l'humain au cœur de chaque décision et veille au respect des droits
                            des retraités et de leurs familles.
                        </p>

                        <p>
                            Sa mission s’inscrit dans la volonté de faire de la Pension Civile un modèle
                            de gestion moderne, fiable et accessible à tous.
                        </p>
                    </div>

                    <!-- Signature -->
                    <div class="pt-8 mt-6 border-t border-gray-200">
                        <p class="text-lg font-semibold text-gray-900">Mme Esther Musac JEUDY</p>
                        <p class="text-gray-600 font-medium">
                            Directrice Générale<br>
                            <span class="text-sm text-gray-500">Direction de la Pension Civile</span>
                        </p>
                    </div>

                </div>

            </div>
        </div>
    </section>
@endif
@endsection
