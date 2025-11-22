@extends('layouts.main')

@section('title', 'FAQ')

@section('content')
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card-shadow {
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1), 0 10px 20px -5px rgba(0, 0, 0, 0.04);
        }

        .faq-card {
            transition: all 0.3s ease;
        }

        .faq-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .icon-container {
            transition: all 0.3s ease;
        }

        .faq-item:hover .icon-container {
            transform: scale(1.1);
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }

        .faq-item:hover .icon-container i {
            color: white;
        }

        .faq-question-btn {
            transition: all 0.3s ease;
        }

        .faq-question-btn:hover {
            color: #1e40af;
        }

        .faq-content {
            display: none;
        }

        .faq-open .faq-content {
            display: block;
        }

        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #d1d5db, transparent);
            margin: 2rem 0;
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <!-- HEADER -->
{{--     <section class="gradient-bg text-white py-16 fade-in">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">FAQ – Pension Civile</h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                Retrouvez les réponses aux questions les plus fréquentes concernant la Pension Civile de retraite.
            </p>
        </div>
    </section> --}}

    <!-- FAQ CONTENT -->
    <section class="relative fade-in">
        <div class="max-w-7xl mx-auto px-6">
            <div class="bg-white rounded-2xl card-shadow overflow-hidden mt-12">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">

                    <!-- LEFT PANEL -->
                    <div class="p-10 bg-gradient-to-br from-gray-50 to-blue-50 border-r border-gray-200">
                        <div class="mb-10">
                            <h2 class="text-3xl font-bold gradient-text mb-3">Foire Aux Questions</h2>
                            <p class="text-gray-600">Toutes les informations essentielles sur vos droits et démarches.</p>
                        </div>

                        <div class="space-y-8">

                            <div class="faq-item flex items-start gap-4">
                                <div class="icon-container w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user-check text-blue-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-1">Éligibilité</h3>
                                    <p class="text-gray-600">Conditions d'accès, années de service, et statut requis.</p>
                                </div>
                            </div>

                            <div class="faq-item flex items-start gap-4">
                                <div class="icon-container w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-file-alt text-purple-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-1">Documents requis</h3>
                                    <p class="text-gray-600">Liste des pièces à fournir pour constituer votre dossier.</p>
                                </div>
                            </div>

                            <div class="faq-item flex items-start gap-4">
                                <div class="icon-container w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-calculator text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-1">Calcul de la pension</h3>
                                    <p class="text-gray-600">Méthode de calcul selon le décret du 09 octobre 2015.</p>
                                </div>
                            </div>

                            <div class="faq-item flex items-start gap-4">
                                <div class="icon-container w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-money-check-alt text-yellow-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-1">Paiement</h3>
                                    <p class="text-gray-600">Calendrier et modalités de versement.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- RIGHT PANEL – FAQ ACCORDIONS -->
                    <div class="p-10">

                        <!-- FAQ ITEM 1 -->
                        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-4 faq-card">
                            <button class="faq-question-btn w-full flex justify-between items-center text-left text-lg font-semibold text-gray-800"
                                onclick="toggleFAQ(this)">
                                Qui peut bénéficier de la Pension Civile ?
                                <i class="fas fa-plus text-gray-500"></i>
                            </button>
                            <div class="faq-content mt-4 text-gray-600">
                                La Pension Civile est destinée aux fonctionnaires ayant réalisé le nombre
                                d’années de service requis et remplissant les conditions légales prévues.
                            </div>
                        </div>

                        <!-- FAQ ITEM 2 -->
                        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-4 faq-card">
                            <button class="faq-question-btn w-full flex justify-between items-center text-left text-lg font-semibold text-gray-800"
                                onclick="toggleFAQ(this)">
                                Quels documents sont nécessaires pour déposer une demande ?
                                <i class="fas fa-plus text-gray-500"></i>
                            </button>
                            <div class="faq-content mt-4 text-gray-600">
                                Les principaux documents sont : une pièce d'identité, les états de service,
                                le dernier bulletin de salaire, et le formulaire officiel dûment rempli.
                            </div>
                        </div>

                        <!-- FAQ ITEM 3 -->
                        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-4 faq-card">
                            <button class="faq-question-btn w-full flex justify-between items-center text-left text-lg font-semibold text-gray-800"
                                onclick="toggleFAQ(this)">
                                Comment est calculée la Pension Civile ?
                                <i class="fas fa-plus text-gray-500"></i>
                            </button>
                            <div class="faq-content mt-4 text-gray-600">
                                Le calcul se base sur la moyenne des 60 meilleurs mois de salaire,
                                conformément au décret du 09 octobre 2015.
                            </div>
                        </div>

                        <!-- FAQ ITEM 4 -->
                        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-4 faq-card">
                            <button class="faq-question-btn w-full flex justify-between items-center text-left text-lg font-semibold text-gray-800"
                                onclick="toggleFAQ(this)">
                                À quelle fréquence la pension est-elle versée ?
                                <i class="fas fa-plus text-gray-500"></i>
                            </button>
                            <div class="faq-content mt-4 text-gray-600">
                                Les pensions sont généralement versées mensuellement selon le calendrier
                                établi par le MEF.
                            </div>
                        </div>

                        <!-- FAQ ITEM 5 -->
                        <div class="bg-white border border-gray-200 rounded-xl p-6 faq-card">
                            <button class="faq-question-btn w-full flex justify-between items-center text-left text-lg font-semibold text-gray-800"
                                onclick="toggleFAQ(this)">
                                Que faire si aucune réponse ne correspond à ma question ?
                                <i class="fas fa-plus text-gray-500"></i>
                            </button>
                            <div class="faq-content mt-4 text-gray-600">
                                Vous pouvez contacter notre service d'assistance ou vous rendre dans nos bureaux
                                pour obtenir une aide personnalisée.
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function toggleFAQ(btn) {
            const content = btn.nextElementSibling;
            const icon = btn.querySelector("i");

            if (content.style.display === "block") {
                content.style.display = "none";
                icon.classList.replace("fa-minus", "fa-plus");
            } else {
                content.style.display = "block";
                icon.classList.replace("fa-plus", "fa-minus");
            }
        }
    </script>
@endsection
