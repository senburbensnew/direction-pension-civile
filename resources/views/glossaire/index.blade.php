@extends('layouts.main')

@section('content')
<style>
    .term-card {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .term-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(to bottom, #3b82f6, #1e40af);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .term-card:hover::before {
        transform: scaleY(1);
    }

    .term-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15);
    }

    .category-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }

    .gradient-text {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .search-box {
        transition: all 0.3s ease;
    }

    .search-box:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .fade-in {
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <header class="mb-10 text-center fade-in">
        <h1 class="text-4xl font-bold gradient-text mb-3">Glossaire de la Pension Civile</h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">
            Retrouvez la définition des termes essentiels utilisés dans le domaine de la pension civile de retraite.
        </p>

        <!-- Search -->
        <div class="mt-8 max-w-2xl mx-auto">
            <div class="relative">
                <input type="text" id="search-terms" placeholder="Rechercher un terme..."
                    class="w-full p-4 pl-12 rounded-xl border border-gray-300 search-box focus:outline-none">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- Glossary Grid -->
    <section class="fade-in">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="terms-list">

            <!-- TERM 1 -->
            <div class="term-card bg-white border border-gray-200 rounded-xl p-5">
                <span class="category-badge bg-blue-100 text-blue-800">Retraite</span>
                <div class="flex items-start mb-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-piggy-bank text-blue-600 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Pension</h3>
                </div>
                <p class="text-gray-600 leading-relaxed">
                    Montant versé chaque mois à un fonctionnaire retraité, en compensation de ses années de service.
                </p>
            </div>

            <!-- TERM 2 -->
            <div class="term-card bg-white border border-gray-200 rounded-xl p-5">
                <span class="category-badge bg-green-100 text-green-800">Finance</span>
                <div class="flex items-start mb-3">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-coins text-green-600 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Cotisation</h3>
                </div>
                <p class="text-gray-600 leading-relaxed">
                    Prélèvement obligatoire effectué sur le salaire pour financer la pension civile et les droits futurs.
                </p>
            </div>

            <!-- TERM 3 -->
            <div class="term-card bg-white border border-gray-200 rounded-xl p-5">
                <span class="category-badge bg-purple-100 text-purple-800">Agent</span>
                <div class="flex items-start mb-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-id-card text-purple-600 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Affilié</h3>
                </div>
                <p class="text-gray-600 leading-relaxed">
                    Fonctionnaire inscrit au régime de pension civile et cotisant régulièrement selon la loi.
                </p>
            </div>

            <!-- TERM 4 -->
            <div class="term-card bg-white border border-gray-200 rounded-xl p-5">
                <span class="category-badge bg-amber-100 text-amber-800">Calcul</span>
                <div class="flex items-start mb-3">
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-calculator text-amber-600 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Salaire de référence</h3>
                </div>
                <p class="text-gray-600 leading-relaxed">
                    Salaire moyen calculé à partir des 60 meilleurs mois de rémunération, servant au calcul de la pension.
                </p>
            </div>

            <!-- TERM 5 -->
            <div class="term-card bg-white border border-gray-200 rounded-xl p-5">
                <span class="category-badge bg-red-100 text-red-800">Invalidité</span>
                <div class="flex items-start mb-3">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-heartbeat text-red-600 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Retraite anticipée</h3>
                </div>
                <p class="text-gray-600 leading-relaxed">
                    Départ à la retraite avant l’âge légal, généralement pour raisons de santé ou incapacité.
                </p>
            </div>

            <!-- TERM 6 -->
            <div class="term-card bg-white border border-gray-200 rounded-xl p-5">
                <span class="category-badge bg-indigo-100 text-indigo-800">Administration</span>
                <div class="flex items-start mb-3">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-building text-indigo-600 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Direction de la Pension Civile</h3>
                </div>
                <p class="text-gray-600 leading-relaxed">
                    Institution publique chargée de la gestion administrative, financière et réglementaire des pensions civiles.
                </p>
            </div>

        </div>
    </section>
</div>

<script>
    // Live search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-terms');
        const cards = document.querySelectorAll('.term-card');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();

            cards.forEach(card => {
                const text = card.querySelector('h3').textContent.toLowerCase() +
                             card.querySelector('p').textContent.toLowerCase();

                card.style.display = text.includes(query) ? 'block' : 'none';
            });
        });
    });
</script>
@endsection
