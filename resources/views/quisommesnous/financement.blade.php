@extends('layouts.main')

@section('title', 'Financement de la Pension Civile')

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
            <h1 class="text-4xl font-bold gradient-text mt-2 mb-3">Financement de la Pension Civile</h1>
            <p class="text-gray-600 text-lg max-w-3xl mx-auto">
                Le financement de la Direction de la Pension Civile repose sur plusieurs sources,
                garantissant la pérennité et l’équilibre du système de retraite des fonctionnaires.
            </p>
        </div>

        {{-- Sources --}}
        <section>
            <h2 class="text-xl font-bold text-gray-900 mb-4">Sources de financement</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Cotisations des fonctionnaires</h3>
                            <p class="text-sm text-gray-600">
                                Prélèvements obligatoires sur les salaires des agents publics.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-landmark text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Contributions de l’État</h3>
                            <p class="text-sm text-gray-600">
                                Subventions directes du gouvernement pour garantir le versement des pensions.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-chart-line text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Revenus d’investissement</h3>
                            <p class="text-sm text-gray-600">
                                Placements financiers réalisés pour optimiser la gestion des fonds de pension.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-hand-holding-usd text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Autres sources</h3>
                            <p class="text-sm text-gray-600">
                                Contributions spéciales et financements exceptionnels en cas de déséquilibre budgétaire.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Répartition --}}
        <section>
            <h2 class="text-xl font-bold text-gray-900 mb-4">Répartition budgétaire</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 card-shadow p-5 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <i class="fas fa-money-check-alt text-emerald-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Paiement des pensions</h3>
                    <p class="text-sm text-gray-600">Assure le versement régulier aux retraités.</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 card-shadow p-5 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-lg bg-amber-50 flex items-center justify-center">
                        <i class="fas fa-building text-amber-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Gestion administrative</h3>
                    <p class="text-sm text-gray-600">Frais de fonctionnement et gestion du personnel.</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 card-shadow p-5 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <i class="fas fa-piggy-bank text-indigo-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Fonds de réserve</h3>
                    <p class="text-sm text-gray-600">Provision pour garantir la stabilité du système.</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 card-shadow p-5 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-lg bg-sky-50 flex items-center justify-center">
                        <i class="fas fa-cogs text-sky-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Modernisation</h3>
                    <p class="text-sm text-gray-600">Digitalisation et amélioration des services.</p>
                </div>
            </div>
        </section>

        {{-- Transparence --}}
        <section class="bg-white rounded-2xl border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <i class="fas fa-balance-scale text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Transparence et contrôle</h2>
                    <p class="text-gray-600 leading-relaxed">
                        La Direction de la Pension Civile assure une gestion transparente de ses ressources financières,
                        avec des audits réguliers et des rapports publiés annuellement.
                    </p>
                    <a href="{{ route('reports.index') }}"
                       class="inline-flex items-center gap-2 mt-4 text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                        Consulter les rapports <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection
