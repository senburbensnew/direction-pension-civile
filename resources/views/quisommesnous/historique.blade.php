@extends('layouts.main')

@section('title', 'Historique de la Direction de la Pension Civile')

@section('content')
<style>
    .gradient-text {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    .info-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .info-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 4px; height: 100%;
        background: linear-gradient(to bottom, #3b82f6, #1e40af);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }
    .info-card:hover::before { transform: scaleY(1); }
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.12);
    }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Qui sommes-nous</span>
            <h1 class="text-4xl font-bold gradient-text mt-2 mb-3">Historique</h1>
            <p class="text-gray-600 text-lg max-w-3xl mx-auto">
                La Direction de la Pension Civile a été créée pour assurer la gestion efficace des pensions
                des fonctionnaires civils. Depuis sa création, elle a évolué au rythme des réformes
                administratives et économiques.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-6">
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center mb-4">
                    <i class="fas fa-flag text-blue-600 text-lg"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-900 mb-2">Origines et création</h2>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Dès ses débuts, la Direction avait pour mission d’organiser le paiement des pensions
                    aux fonctionnaires retraités et d’assurer la continuité de leurs droits.
                </p>
            </div>

            <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-6">
                <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center mb-4">
                    <i class="fas fa-sync-alt text-amber-600 text-lg"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-900 mb-2">Évolutions et réformes</h2>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Au fil du temps, elle s’est modernisée pour répondre aux nouveaux défis
                    de l’administration publique et améliorer le traitement des dossiers.
                </p>
            </div>

            <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-6">
                <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center mb-4">
                    <i class="fas fa-rocket text-emerald-600 text-lg"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-900 mb-2">Mission aujourd’hui</h2>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Elle poursuit la digitalisation de ses services et l’optimisation des procédures
                    pour une gestion plus rapide, transparente et accessible.
                </p>
            </div>
        </div>

        <section class="bg-white rounded-2xl border border-gray-200 card-shadow p-6 sm:p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Étapes marquantes</h2>
            <ol class="space-y-5">
                <li class="flex gap-4">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center shrink-0">1</div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Automatisation progressive</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Introduction des premiers systèmes de gestion automatisée des dossiers et des paiements.
                        </p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center shrink-0">2</div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Réformes organisationnelles</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Réformes majeures pour améliorer l’efficacité du traitement des dossiers
                            et renforcer la qualité du service rendu.
                        </p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center shrink-0">3</div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Transparence et technologies</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Intégration de nouvelles technologies pour une meilleure transparence
                            et un suivi plus fiable des prestations.
                        </p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center shrink-0">4</div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Partenariats institutionnels</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Collaboration renforcée avec d’autres institutions pour une meilleure
                            prise en charge des retraités et de leurs ayants droit.
                        </p>
                    </div>
                </li>
            </ol>
        </section>

    </div>
</div>
@endsection
