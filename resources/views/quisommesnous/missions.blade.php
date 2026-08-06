@extends('layouts.main')

@section('title', 'Mission et Attributions')

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
            <h1 class="text-4xl font-bold gradient-text mt-2 mb-3">Mission et Attributions</h1>
            <p class="text-gray-600 text-lg max-w-3xl mx-auto">
                La Direction de la Pension Civile assure la gestion, la liquidation et le suivi des droits à pension
                des fonctionnaires civils, dans un cadre de transparence et de modernisation du service public.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-6">
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center mb-4">
                    <i class="fas fa-eye text-blue-600 text-lg"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Notre vision</h2>
                <p class="text-gray-600 leading-relaxed">
                    Assurer la sécurité et la transparence dans le traitement des pensions civiles,
                    afin de garantir à chaque bénéficiaire le respect de ses droits.
                </p>
            </div>

            <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-6">
                <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center mb-4">
                    <i class="fas fa-bullseye text-emerald-600 text-lg"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Notre mission</h2>
                <p class="text-gray-600 leading-relaxed">
                    Traiter, liquider et sécuriser les dossiers de pension, tout en accompagnant
                    les fonctionnaires et retraités dans leurs démarches administratives.
                </p>
            </div>
        </div>

        <section>
            <h2 class="text-xl font-bold text-gray-900 mb-4">Attributions principales</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-folder-open text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Traitement et liquidation</h3>
                            <p class="text-sm text-gray-600">
                                Instruction et liquidation des dossiers de pension civile et prestations associées.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-balance-scale text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Réformes et élaboration</h3>
                            <p class="text-sm text-gray-600">
                                Contribution aux réformes et à l’élaboration des règles en matière de pension civile.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-money-check-alt text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Paiements et contrôle</h3>
                            <p class="text-sm text-gray-600">
                                Gestion des paiements et contrôle des bénéficiaires pour prévenir les irrégularités.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="info-card bg-white rounded-xl border border-gray-200 card-shadow p-5">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-hands-helping text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Conseil et assistance</h3>
                            <p class="text-sm text-gray-600">
                                Accompagnement des fonctionnaires, retraités et ayants droit dans leurs démarches.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Engagement de service</h2>
                    <p class="text-gray-600 leading-relaxed">
                        Nous œuvrons pour un service plus accessible, plus rapide et plus proche des usagers,
                        grâce à la digitalisation des procédures et à une organisation claire des responsabilités.
                    </p>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection
