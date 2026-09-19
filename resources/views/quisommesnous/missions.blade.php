@extends('layouts.main')

@section('title', 'Présentation, mission et attributions, historique')

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Qui sommes-nous</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Présentation, mission et attributions, historique</h1>
        </div>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Présentation de la Direction de la Pension Civile</h2>
            </div>
            <p class="text-gray-700 leading-relaxed">
                La Direction de la Pension Civile (DPC) tire son origine de la nécessité, pour l’État, de prendre en charge les agents publics n’étant plus en âge de travailler. Structure interne du Ministère de l’Économie et des Finances (MEF), elle a la compétence exclusive de gérer le système de retraite des fonctionnaires conformément aux dispositions du décret du 9 octobre 2015 modifiant celui du 18 février 2011 sur la pension civile de retraite.
            </p>
        </section>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-gavel" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Cadre légal des activités de la DPC</h2>
            </div>
            <p class="text-gray-700 leading-relaxed">
                La Constitution haïtienne jette les bases de l’édification d’une structure telle que la DPC en ses articles 22, 35, 48 et 220. À côté du décret du 13 mars 1987 modifiant celui du 31 octobre 1983 portant réorganisation du Ministère de l’Économie et des Finances (MEF), la législation régissant la pension civile de retraite a subi dans le temps diverses modifications, dont la dernière en date est celle apportée par le décret du 9 octobre 2015.
            </p>
        </section>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8 space-y-5">
            <div class="flex items-center gap-3">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-bullseye" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Missions et attributions</h2>
            </div>
            <p class="text-gray-700 leading-relaxed">
                « La Direction de la Pension est chargée de l’application de la loi régissant la Pension Civile et la Pension Militaire. Elle établit et maintient à jour la liste des pensionnaires, étudie les dossiers de demande et recommande toute liquidation de pension », conformément aux dispositions de l’article 17 de la loi organique du Ministère de l’Économie et des Finances.
            </p>
            <p class="text-gray-700 leading-relaxed">En outre, la DPC s’occupe de :</p>
            <ul class="space-y-2.5 text-gray-700 text-base leading-relaxed">
                @foreach([
                    'La gestion et l’administration du plan de retraite de l’administration publique en Haïti.',
                    'La gestion des comptes de pension civile.',
                    'La liquidation des pensions de retraite des agents publics.',
                    'Le paiement des prestations aux pensionnés de l’État.',
                    'La gestion de l’assurance maladie des pensionnés de l’État.',
                    'Le développement des procédures et des outils capables d’accroître la qualité du service aux adhérents.',
                    'La gestion du PRAP par la DPC inclut aussi le recouvrement des cotisations de retraite des employés des institutions publiques affiliées. Elle assure la prise en charge des pensionnés de l’État haïtien sur tout le territoire national via l’Unité de Coordination des Directions Départementales (UCDD).',
                    'Les pensionnés résidant à l’extérieur du pays transmettent des mandats consulaires pour toute communication à la DPC.',
                ] as $item)
                    <li class="flex items-start gap-2.5">
                        <i class="fa-solid fa-check text-navy mt-1 text-xs" aria-hidden="true"></i>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="flex items-center gap-3 pt-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-user-group" aria-hidden="true"></i>
                </span>
                <h3 class="text-lg font-bold text-navy">Public cible du plan de retraite de l’administration publique</h3>
            </div>
            <ul class="space-y-2.5 text-gray-700 leading-relaxed">
                <li class="flex items-start gap-2.5">
                    <i class="fa-solid fa-check text-navy mt-1 text-xs" aria-hidden="true"></i>
                    <span>Les fonctionnaires de l’État</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <i class="fa-solid fa-check text-navy mt-1 text-xs" aria-hidden="true"></i>
                    <span>Les agents des organismes autonomes de l’État et des entreprises publiques n’ayant pas un régime de retraite propre</span>
                </li>
            </ul>
        </section>

        @php
            $datesImportantes = [
                ['annee' => '1843', 'texte' => 'Liquidation d’une pension viagère à Claire Heureuse.', 'images' => [
                    ['src' => 'images/historique/decret-1843-pension-viagere.jpg', 'alt' => 'Décret de 1843 accordant une pension viagère'],
                ]],
                ['annee' => '1864', 'texte' => 'Adoption d’un mode uniforme de liquidation des pensions.', 'images' => [
                    ['src' => 'images/historique/loi-1864-pensions-civiles.png', 'alt' => 'Loi du 19 novembre 1864 sur les pensions civiles'],
                    ['src' => 'images/historique/loi-pension-civile-militaire.png', 'alt' => 'Loi de 1864 sur la pension civile et militaire'],
                ]],
                ['annee' => '1884', 'texte' => 'Condition d’éligibilité à la retraite : 60 ans d’âge et 30 années de carrière minimum.'],
                ['annee' => '1997', 'texte' => 'La DPC fournit un service de guichet (et non de liquidation) aux pensionnaires militaires et à ceux de la minoterie et du BNDAI.'],
                ['annee' => '1998', 'texte' => 'Programme de départ à la retraite.'],
                ['annee' => '2004', 'texte' => 'Le principe de remboursement des cotisations au fonds de pension est ouvert à des conditions spécifiques.'],
                ['annee' => '2005', 'texte' => 'Prêt / aval aux pensionnés par la Banque Populaire Haïtienne (BPH).'],
                ['annee' => '2007', 'texte' => 'Extension de la couverture d’assurance accordée aux agents publics pour les pensionnaires.'],
                ['annee' => '2007', 'texte' => 'La DPC fournit un service de guichet aux footballeurs de la sélection nationale de 1974.'],
                ['annee' => '2008', 'texte' => 'La Direction de la Pension Civile consent des avances aux agents en service actif pour le paiement de l’impôt sur le revenu.'],
                ['annee' => '2009', 'texte' => 'Intégration des virements bancaires comme moyen de paiement des pensionnés par la DPC.'],
                ['annee' => '2015', 'texte' => 'Aval aux pensionnés par la DPC.'],
                ['annee' => '2015', 'texte' => 'Prêt au logement.'],
            ];
        @endphp

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Dates importantes</h2>
            </div>
            <p class="text-base text-gray-700 mb-6">Chronogramme interactif 1843 — 2015. Déplacez et zoomez pour parcourir l’historique. Cliquez une date pour afficher le détail.</p>

            <div id="dpc-chronogramme"
                 class="dpc-chronogramme mb-10"
                 data-events="{{ json_encode($datesImportantes, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"></div>

            <div class="flex items-center gap-3 mb-6">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-list" aria-hidden="true"></i>
                </span>
                <h3 class="text-lg font-bold text-navy">Repères détaillés</h3>
            </div>
            <ol class="relative space-y-6">
                <span class="absolute left-[1.15rem] sm:left-[5.35rem] top-3 bottom-3 w-px bg-slate-200" aria-hidden="true"></span>
                @foreach($datesImportantes as $event)
                    <li id="date-importante-{{ $loop->iteration }}" class="relative scroll-mt-28">
                        <div class="grid grid-cols-[2.5rem_1fr] sm:grid-cols-[6.5rem_1fr] gap-4 sm:gap-6 items-start">
                            <div class="relative z-10 flex justify-center sm:justify-end pt-1">
                                <span class="flex flex-col items-center justify-center w-10 h-10 sm:w-[4.75rem] sm:h-auto sm:py-2.5 bg-navy text-white">
                                    <span class="hidden sm:block text-[9px] uppercase tracking-[0.16em] text-white/70 leading-none mb-1">Année</span>
                                    <span class="text-[11px] sm:text-base font-bold leading-none">{{ $event['annee'] }}</span>
                                </span>
                            </div>
                            <article class="pt-1 sm:pt-2">
                                <p class="text-gray-800 leading-relaxed text-base">{{ $event['texte'] }}</p>
                                @if(!empty($event['images']))
                                    @foreach($event['images'] as $image)
                                        <figure class="mt-5">
                                            <img src="{{ asset($image['src']) }}"
                                                 alt="{{ $image['alt'] }}"
                                                 class="w-full max-w-2xl bg-white">
                                            <figcaption class="mt-2 text-sm text-gray-600">{{ $image['alt'] }}</figcaption>
                                        </figure>
                                    @endforeach
                                @endif
                            </article>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

    </div>
</div>
@endsection
