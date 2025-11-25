@extends('layouts.main')

@section('title', 'Accueil')

@section('content')
    <style>
        /* Garder uniquement les styles généraux, supprimer les styles des graphiques */
        .gradient-bg {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card-shadow {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15);
        }

        .stat-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, #3b82f6, #1e40af);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(245, 158, 11, 0.4);
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pulse-slow {
            animation: pulse 3s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .carousel-slide-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.4);
        }

        .carousel-text-content {
            text-align: center;
            color: white;
            z-index: 2;
            padding: 2rem;
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            backdrop-filter: blur(10px);
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 1.5rem;
        }

        .swiper-pagination-bullet {
            background: white;
            opacity: 0.5;
        }

        .swiper-pagination-bullet-active {
            background: #f59e0b;
            opacity: 1;
        }
    </style>

    <!-- HERO + SIDEBAR -->
    <section class="py-0 -mt-6 gap-6 flex flex-col lg:flex-row lg:justify-between items-start bg-white w-full">
        <x-carousel>
            <!-- Slide 1 -->
            <div class="swiper-slide">
                <div class="gradient-bg w-full h-full flex items-center justify-center">
                    <div class="carousel-text-content fade-in">
                        <h1 class="text-4xl md:text-6xl font-bold mb-6">Direction de la Pension Civile</h1>
                        <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
                            Votre partenaire de confiance pour une retraite sereine et sécurisée
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <button class="btn-primary text-white px-8 py-4 rounded-lg font-semibold text-lg">
                                Découvrir nos services
                            </button>

                            <a href="{{ route('simulateur-calcul') }}">
                                <button class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-colors">
                                    Calculer ma pension
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="swiper-slide relative">
                <img src="{{ asset('images/carousel/carousel1.png') }}" alt="Direction" class="w-full h-full object-cover">

                <div class="carousel-slide-content">
                    <div class="carousel-text-content">
                        <h2 class="text-4xl md:text-5xl font-bold mb-4">Expertise et Accompagnement</h2>
                        <p class="text-xl text-white mb-6">Notre équipe vous guide à chaque étape</p>
                        <button class="btn-primary text-white px-8 py-3 rounded-lg font-semibold">
                            En savoir plus
                        </button>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="swiper-slide relative">
                <img src="{{ asset('images/carousel/carousel2.png') }}" alt="Services" class="w-full h-full object-cover">

                <div class="carousel-slide-content">
                    <div class="carousel-text-content">
                        <h2 class="text-4xl md:text-5xl font-bold mb-4">Votre Avenir Sécurisé</h2>
                        <p class="text-xl text-white mb-6">Des solutions adaptées à vos besoins</p>
                        <button class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50">
                            Nos solutions
                        </button>
                    </div>
                </div>
            </div>
        </x-carousel>

        <div class="mt-6 w-full lg:w-auto">
            <x-presentation />
        </div>
    </section>

    <!-- ABOUT -->
    <section class="py-16 bg-white fade-in">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row items-center gap-12">

                <!-- Image -->
                <div class="relative w-full lg:w-1/2">
                    <div class="rounded-2xl overflow-hidden card-shadow hover-lift">
                        <img src="{{ asset('images/photo_2025-11-21_06-42-22.jpg') }}" class="w-full h-auto">
                    </div>

                    <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl p-6 card-shadow hidden lg:block">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600 mb-1">20+</div>
                            <div class="text-sm text-gray-600">Ans d'expérience</div>
                        </div>
                    </div>
                </div>

                <!-- Text -->
                <div class="w-full lg:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6 gradient-text">
                        Votre guide vers la Pension Civile
                    </h2>

                    <p class="mb-6 text-gray-600 text-lg leading-relaxed">
                        La Direction de la Pension Civile accompagne les fonctionnaires et retraités dans toutes leurs démarches.
                    </p>

                    <p class="mb-6 italic text-gray-500 text-lg border-l-4 border-orange-500 pl-4">
                        "Assurer vos droits et votre avenir, notre priorité."
                    </p>

                    <div class="flex items-center gap-3 text-gray-700 mb-8">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check text-orange-500"></i>
                        </div>
                        <span class="text-lg">Expertise et accompagnement personnalisé</span>
                    </div>

                    <button class="btn-primary text-white px-8 py-3 rounded-lg font-semibold">
                        En savoir plus
                    </button>
                </div>

            </div>
        </div>
    </section>

    <!-- MISSIONS + STATS -->
    <section class="py-16 gradient-bg text-white fade-in">
        <div class="container mx-auto px-4">

            <h2 class="text-3xl md:text-4xl font-bold mb-12 text-center">Nos missions et valeurs</h2>

            <div class="grid lg:grid-cols-3 gap-8 mb-16">

                <!-- Vision -->
                <div class="bg-white text-gray-800 rounded-2xl p-8 card-shadow hover-lift">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-eye text-blue-600 text-2xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4">Notre Vision</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Assurer la sécurité et la transparence dans le traitement des pensions civiles.
                    </p>
                </div>

                <!-- Mission -->
                <div class="bg-white text-gray-800 rounded-2xl p-8 card-shadow hover-lift">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-bullseye text-green-600 text-2xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4">Notre Mission</h3>

                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            Traitement et liquidation des dossiers de pension
                        </li>

                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            Élaboration et réformes en matière de pension civile
                        </li>

                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            Gestion des paiements et contrôle des bénéficiaires
                        </li>

                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            Conseil et assistance aux fonctionnaires et retraités
                        </li>
                    </ul>
                </div>

                <!-- Video CTA -->
                <div class="flex flex-col items-center justify-center bg-white/10 rounded-2xl p-8 backdrop-blur-sm border border-white/20">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mb-6 pulse-slow">
                        <i class="fas fa-play text-orange-500 text-2xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4 text-center">Découvrez notre institution</h3>

                    <p class="text-blue-100 text-center mb-6">
                        Visionnez notre vidéo de présentation
                    </p>

                    <button class="btn-primary text-white px-8 py-3 rounded-lg font-semibold">
                        Visionner la vidéo
                    </button>
                </div>

            </div>

            <!-- STATS -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-8 text-center">

                <div class="stat-card bg-white/10 rounded-2xl p-6 backdrop-blur-sm border border-white/20">
                    <div class="text-3xl font-bold text-white mb-2">680+</div>
                    <div class="text-blue-100">Bénéficiaires Confiants</div>
                </div>

                <div class="stat-card bg-white/10 rounded-2xl p-6 backdrop-blur-sm border border-white/20">
                    <div class="text-3xl font-bold text-white mb-2">1,354</div>
                    <div class="text-blue-100">Dossiers Traités</div>
                </div>

                <div class="stat-card bg-white/10 rounded-2xl p-6 backdrop-blur-sm border border-white/20">
                    <div class="text-3xl font-bold text-white mb-2">97%</div>
                    <div class="text-blue-100">Taux de Satisfaction</div>
                </div>

                <div class="stat-card bg-white/10 rounded-2xl p-6 backdrop-blur-sm border border-white/20">
                    <div class="text-3xl font-bold text-white mb-2">20+</div>
                    <div class="text-blue-100">Années d'Expérience</div>
                </div>

            </div>
        </div>
    </section>

    <!-- TEAM -->
    <section class="py-16 bg-gray-50 fade-in">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold mb-6 text-center gradient-text">
                Rencontrez notre équipe d'experts
            </h2>

            <p class="text-gray-600 text-lg text-center mb-12 max-w-2xl mx-auto">
                Des professionnels dévoués à votre service
            </p>

            <div class="grid md:grid-cols-2 gap-12 items-center">

                <div class="rounded-2xl overflow-hidden card-shadow hover-lift">
                    <img src="{{ asset('images/photo_2025-11-21_13-09-25.jpg') }}" class="w-full h-auto">
                </div>

                <div class="flex flex-col justify-center">
                    <p class="text-gray-600 mb-6 text-lg leading-relaxed">
                        Notre équipe d'experts accompagne chaque bénéficiaire avec professionnalisme et dévouement.
                    </p>

                    <div class="grid grid-cols-2 gap-4 mb-8">

                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-tie text-blue-600"></i>
                            </div>
                            Experts certifiés
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-green-600"></i>
                            </div>
                            Données sécurisées
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-purple-600"></i>
                            </div>
                            Accompagnement continu
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-hand-holding-heart text-orange-600"></i>
                            </div>
                            Service personnalisé
                        </div>

                    </div>

                    <button class="btn-primary text-white px-8 py-3 rounded-lg font-semibold w-fit">
                        Découvrir l'équipe
                    </button>
                </div>

            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 gradient-bg text-white fade-in">
        <div class="container mx-auto px-4 text-center">

            <h2 class="text-3xl md:text-4xl font-bold mb-6">Informez-vous sur vos droits</h2>

            <p class="mb-8 text-blue-100 text-lg max-w-3xl mx-auto leading-relaxed">
                Découvrez les informations essentielles pour préparer votre avenir en toute sérénité.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button class="btn-primary text-white px-8 py-4 rounded-lg font-semibold text-lg">
                    En savoir plus
                </button>

                <button class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-50">
                    Prendre rendez-vous
                </button>
            </div>
        </div>
    </section>

    <!-- QUICK LINKS -->
    <section class="py-16 bg-white fade-in">
        <div class="container mx-auto px-4">

            <h2 class="text-3xl md:text-4xl font-bold mb-12 text-center gradient-text">
                Accès Rapide
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

                <!-- Simulateur -->
                <div class="bg-blue-50 rounded-2xl p-8 text-center card-shadow hover-lift">
                    <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calculator text-blue-600 text-2xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4">Simulateur</h3>

                    <p class="text-gray-600 mb-6">
                        Estimez votre future pension en quelques clics
                    </p>

                    <a href="{{ route('simulateur-calcul') }}" class="text-blue-600 font-semibold hover:text-blue-800">
                        Calculer ma pension →
                    </a>
                </div>

                <!-- Documents -->
                <div class="bg-green-50 rounded-2xl p-8 text-center card-shadow hover-lift">
                    <div class="w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-alt text-green-600 text-2xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4">Documents légaux</h3>

                    <p class="text-gray-600 mb-6">
                        Accédez aux documents officiels
                    </p>

                    <a href="{{ route('textes_documents_legaux') }}" class="text-green-600 font-semibold hover:text-green-800">
                        Consulter les documents →
                    </a>
                </div>

                <!-- FAQ -->
                <div class="bg-purple-50 rounded-2xl p-8 text-center card-shadow hover-lift">
                    <div class="w-16 h-16 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-question-circle text-purple-600 text-2xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4">FAQ & Support</h3>

                    <p class="text-gray-600 mb-6">
                        Trouvez des réponses rapidement
                    </p>

                    <a href="{{ route('faq.index') }}" class="text-purple-600 font-semibold hover:text-purple-800">
                        Obtenir de l'aide →
                    </a>
                </div>

                <!-- Glossaire -->
                <div class="bg-yellow-50 rounded-2xl p-8 text-center card-shadow hover-lift">
                    <div class="w-16 h-16 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book text-yellow-600 text-2xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4">Glossaire</h3>

                    <p class="text-gray-600 mb-6">
                        Comprenez les termes clés
                    </p>

                    <a href="{{ route('glossaire') }}" class="text-yellow-600 font-semibold hover:text-yellow-800">
                        Consulter le glossaire →
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- GRAPH SECTION COMPONENT -->
    <x-graph-section />

    <!-- MODULES -->
    <section class="py-12 bg-white fade-in">
        <div class="container mx-auto px-4">

            <h2 class="text-3xl md:text-4xl font-bold mb-8 text-center gradient-text">
                Nouveautés
            </h2>

            <x-nouveautes />
        </div>
    </section>

    <x-mediatheque />

@endsection
