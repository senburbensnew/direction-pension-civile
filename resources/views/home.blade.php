@extends('layouts.main')

@section('title', 'Accueil')

@section('content')
    <style>
        /* Amélioration des contrastes avec une approche plus douce */
        .gradient-bg {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
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
            height: 3px;
            background: linear-gradient(to right, #3b82f6, #1e40af);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px -5px rgba(245, 158, 11, 0.3);
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pulse-slow {
            animation: pulse 3s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
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
            width: 45px;
            height: 45px;
            border-radius: 50%;
            backdrop-filter: blur(8px);
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 1.25rem;
        }

        .swiper-pagination-bullet {
            background: white;
            opacity: 0.6;
            width: 10px;
            height: 10px;
        }

        .swiper-pagination-bullet-active {
            background: #f59e0b;
            opacity: 1;
        }

        /* Styles pour la section événements */
        .event-card {
            transition: all 0.3s ease;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15);
        }

        .event-date {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 0.375rem;
            font-weight: 600;
            z-index: 2;
            font-size: 0.8rem;
        }

        /* Amélioration subtile des contrastes */
        .text-softer-gray {
            color: #6b7280;
        }

        .text-medium-gray {
            color: #4b5563;
        }

        .bg-soft-white {
            background-color: #fafafa;
        }

        .card-border-soft {
            border: 1px solid #f3f4f6;
        }

        .hover-lift-soft:hover {
            transform: translateY(-2px);
        }

        /* Focus states subtils */
        .focus-soft:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 1px;
        }

        /* Styles pour le nouveau carrousel d'images */
        .image-carousel-container {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
        }

        .image-carousel-slide {
            transition: transform 0.8s ease-in-out;
        }

        .image-carousel-slide img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .carousel-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            color: white;
            padding: 2rem;
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .image-carousel-slide:hover .carousel-overlay {
            transform: translateY(0);
        }

        /* Animation automatique pour le carrousel */
        @keyframes slide {
            0%, 20% { transform: translateX(0); }
            25%, 45% { transform: translateX(-100%); }
            50%, 70% { transform: translateX(-200%); }
            75%, 95% { transform: translateX(-300%); }
            100% { transform: translateX(-400%); }
        }

        .auto-carousel {
            animation: slide 25s infinite;
        }

        .auto-carousel:hover {
            animation-play-state: paused;
        }
    </style>

<div class="py-0">
    <!-- HERO + SIDEBAR -->
    <section class="py-0 gap-6 flex flex-col lg:flex-row lg:justify-between items-end bg-gray-50 w-full">
        <x-carousel>
            <!-- Slide 1 -->
            <div class="swiper-slide">
                <div class="gradient-bg w-full h-full flex items-center justify-center">
                    <div class="carousel-text-content fade-in">
                        <h1 class="text-4xl md:text-6xl font-bold mb-6 text-white">Direction de la Pension Civile</h1>
                        <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
                            Votre partenaire de confiance pour une retraite sereine et sécurisée
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('simulateur-calcul') }}" class="focus-soft">
                                <button class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-colors">
                                    Calculer ma pension
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="swiper-slide relative">
                <img src="{{ asset('images/carousel/KEV_6804.jpg') }}"
                    alt="Direction"
                    class="h-80 w-auto object-cover mx-auto">
            </div>

            <div class="swiper-slide relative">
                <img src="{{ asset('images/carousel/KEV_7157.jpg') }}"
                    alt="Direction"
                    class="h-80 w-auto object-cover mx-auto">
            </div>

            <div class="swiper-slide relative">
                <img src="{{ asset('images/carousel/KEV_7055.jpg') }}"
                    alt="Direction"
                    class="h-80 w-auto object-cover mx-auto">
            </div>

            <div class="swiper-slide relative">
                <img src="{{ asset('images/carousel/KEV_7043.jpg') }}"
                    alt="Direction"
                    class="h-80 w-auto object-cover mx-auto">
            </div>
        </x-carousel>

        <div class="w-full lg:w-auto">
            <x-presentation />
        </div>
    </section>

{{--     <section class="py-0 -mt-6 gap-6 flex flex-col lg:flex-row lg:justify-between items-start bg-gray-50 w-full">
        <!-- Carousel -->
        <x-carousel>
            <!-- Slide 1: Text Slide -->
            <div class="swiper-slide">
                <div class="gradient-bg w-full h-full flex items-center justify-center">
                    <div class="carousel-text-content fade-in text-center px-4">
                        <h1 class="text-4xl md:text-6xl font-bold mb-6 text-white">Direction de la Pension Civile</h1>
                        <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
                            Votre partenaire de confiance pour une retraite sereine et sécurisée
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('simulateur-calcul') }}">
                                <button class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-colors">
                                    Calculer ma pension
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Slides -->
            @foreach (['KEV_6804','KEV_7157','KEV_7055','KEV_7043'] as $img)
            <div class="swiper-slide">
                <img src="{{ asset('images/carousel/' . $img . '.jpg') }}"
                    alt="Direction"
                    class="w-full h-64 md:h-80 lg:h-96 object-cover rounded-lg mx-auto">
            </div>
            @endforeach
        </x-carousel>

        <!-- Presentation -->
        <div class="mt-6 w-full lg:w-auto">
            <x-presentation />
        </div>
    </section> --}}



    <!-- ABOUT -->
    <section class="py-8 bg-white fade-in">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <!-- Image -->
                <div class="relative w-full lg:w-1/2">
                    <div class="rounded-2xl overflow-hidden">
                        {{-- <img src="{{ asset('images/photo_2025-12-02_17-53-04.jpg') }}" class="w-full h-auto"> --}}
                        <img src="{{ asset('images/photo_2025-12-02_17-53-04.jpg') }}"
     class="w-full max-w-md mx-auto h-auto">

                    </div>
                </div>
                <!-- Text -->
                <div class="w-full lg:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6 gradient-text">
                        Votre guide vers la Pension Civile
                    </h2>

                    <p class="mb-6 text-medium-gray text-lg leading-relaxed">
                        La Direction de la Pension Civile accompagne les fonctionnaires et retraités dans toutes leurs démarches.
                    </p>

                    <p class="mb-6 italic text-softer-gray text-lg border-l-4 border-orange-500 pl-4">
                        "Assurer vos droits et votre avenir, notre priorité."
                    </p>

                    <div class="flex items-center gap-3 text-medium-gray mb-8">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check text-orange-500"></i>
                        </div>
                        <span class="text-lg">Expertise et accompagnement</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MISSIONS -->
    <section class="py-8 gradient-bg text-white fade-in">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold mb-12 text-center">Notre vision et notre mission</h2>
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Vision -->
                <div class="bg-white rounded-2xl p-8 card-shadow hover-lift-soft card-border-soft">
                    <div class="flex justify-center">
                        <div class="w-20 h-20 bg-blue-50 rounded-xl flex items-center justify-center mb-4 text-blue-600 text-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                        </div>
                    </div>

                    <h3 class="text-xl font-bold mb-4 text-gray-800">Notre Vision</h3>
                    <p class="text-softer-gray leading-relaxed">
                        Assurer la sécurité et la transparence dans le traitement des pensions civiles.
                    </p>
                </div>
                <!-- Mission -->
                <div class="bg-white rounded-2xl p-8 card-shadow hover-lift-soft card-border-soft">
                    <div class="flex justify-center">
                        <div class="w-20 h-20 bg-green-50 rounded-xl flex items-center justify-center mb-4 text-green-600 text-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-target-icon lucide-target"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                        </div>
                    </div>

                    <h3 class="text-xl font-bold mb-4 text-gray-800">Notre Mission</h3>

                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span class="text-softer-gray">Traitement et liquidation des dossiers de pension</span>
                        </li>

                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span class="text-softer-gray">Élaboration et réformes en matière de pension civile</span>
                        </li>

                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span class="text-softer-gray">Gestion des paiements et contrôle des bénéficiaires</span>
                        </li>

                        <li class="flex items-start gap-3">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span class="text-softer-gray">Conseil et assistance aux fonctionnaires et retraités</span>
                        </li>
                    </ul>
                </div>
                <!-- Video CTA -->
                <div class="flex flex-col items-center justify-center bg-white/10 rounded-2xl pt-8 backdrop-blur-sm border border-white/20">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mb-6 pulse-slow">
                        <i class="fas fa-play text-orange-500 text-2xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4 text-center">Découvrez notre institution</h3>

                    <p class="text-blue-100 text-center mb-6">
                        Visionnez notre vidéo de présentation
                    </p>

                    <x-video-card
                        videoUrl="https://www.youtube.com/watch?v=RM9wJNyCtXo"
                    />
                </div>
            </div>
        </div>
    </section>

    <section class="py-8 bg-gray-50 fade-in">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center gradient-text mb-4">
                Nos Services
            </h2>

            <p class="text-medium-gray text-center text-lg max-w-2xl mx-auto mb-16">
                Nous accompagnons les pensionnaires, fonctionnaires et institutions à chaque étape,
                grâce à des services fiables, rapides et adaptés à leurs besoins.
            </p>

            <div class="grid md:grid-cols-3 gap-8">

                <!-- Pensionnaire -->
                <div class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition duration-300 border border-gray-100 hover:-translate-y-1">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center shadow-md">
                            <i class="fas fa-user text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-semibold text-gray-800">Pensionnaire</h3>
                    </div>

                    <ul class="text-medium-gray space-y-2 leading-snug">
                        <li>
                            <a href="#" class="flex items-center justify-between p-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition focus-soft">
                                Demande de pension de réversion
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center justify-between p-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition focus-soft">
                                Enregistrement de pensionnaire
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center justify-between p-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition focus-soft">
                                Demande d'arrêt de virement
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Fonctionnaire -->
                <div class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition duration-300 border border-gray-100 hover:-translate-y-1">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-green-500 to-green-600 text-white flex items-center justify-center shadow-md">
                            <i class="fas fa-user-check text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-semibold text-gray-800">Fonctionnaire</h3>
                    </div>

                    <ul class="text-medium-gray space-y-2 leading-snug">
                        <li>
                            <a href="#" class="flex items-center justify-between p-2 rounded-lg hover:bg-green-50 hover:text-green-700 transition focus-soft">
                                Demande d'état de carrière
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center justify-between p-2 rounded-lg hover:bg-green-50 hover:text-green-700 transition focus-soft">
                                Demande de pension
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Institution -->
                <div class="group bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition duration-300 border border-gray-100 hover:-translate-y-1">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 text-white flex items-center justify-center shadow-md">
                            <i class="fas fa-building text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-semibold text-gray-800">Institution</h3>
                    </div>

                    <ul class="text-medium-gray space-y-2 leading-snug">
                        <li>
                            <a href="#" class="flex items-center justify-between p-2 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition focus-soft">
                                Demande d'adhésion
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center justify-between p-2 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition focus-soft">
                                Transmission des demandes de pensions
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- STATISTIQUES -->
    <section class="py-8 bg-white fade-in">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center gradient-text mb-4">
                Statistiques de la Pension Civile
            </h2>

            <p class="text-medium-gray text-center text-lg max-w-2xl mx-auto mb-16">
                Un aperçu visuel des données clés liées à la gestion des pensions civiles.
            </p>

            <div class="grid md:grid-cols-3 gap-8">

                <!-- Graphique 1 : Bar Chart -->
                <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition card-border-soft">
                    <h3 class="text-lg font-semibold mb-4 text-center text-gray-800">Pensions traitées / Année</h3>
                    <canvas id="chartBar"></canvas>
                </div>

                <!-- Graphique 2 : Line Chart -->
                <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition card-border-soft">
                    <h3 class="text-lg font-semibold mb-4 text-center text-gray-800">Évolution des demandes</h3>
                    <canvas id="chartLine"></canvas>
                </div>

                <!-- Graphique 3 : Pie Chart -->
                <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition card-border-soft">
                    <h3 class="text-lg font-semibold mb-4 text-center text-gray-800">Répartition des types de pensions</h3>
                    <canvas id="chartPie"></canvas>
                </div>

            </div>
        </div>
    </section>

        <!-- NOUVELLE SECTION: CARROUSEL D'IMAGES AUTOMATIQUE -->
    <section class="py-8 bg-gray-50 fade-in">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold mb-12 text-center gradient-text">
                Notre Institution en Images
            </h2>
            <x-auto-slide-carousel  />
        </div>
    </section>

    <!-- ÉVÉNEMENTS -->
{{--     <section class="py-8 bg-white fade-in">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center gradient-text mb-4">
                Événements
            </h2>

            <p class="text-medium-gray text-center text-lg max-w-2xl mx-auto mb-16">
                Découvrez les derniers événements et actualités de la Direction de la Pension Civile
            </p>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Événement 1 -->
                <div class="event-card bg-white rounded-2xl overflow-hidden card-shadow relative card-border-soft">
                    <div class="event-date">
                        15 Déc 2024
                    </div>
                    <img
                        src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=800&q=60"
                        alt="Séminaire annuel"
                        class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2 text-gray-800">Séminaire Annuel des Retraités</h3>
                        <p class="text-softer-gray mb-4">Échange et partage sur les nouvelles réformes des pensions civiles.</p>
                        <a href="#" class="text-blue-600 font-semibold hover:text-blue-800 flex items-center gap-2 focus-soft">
                            En savoir plus <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Événement 2 -->
                <div class="event-card bg-white rounded-2xl overflow-hidden card-shadow relative card-border-soft">
                    <div class="event-date">
                        10 Jan 2025
                    </div>
                    <img
                        src="https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&w=800&q=60"
                        alt="Atelier formation"
                        class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2 text-gray-800">Atelier de Formation</h3>
                        <p class="text-softer-gray mb-4">Formation sur les nouvelles procédures de demande de pension en ligne.</p>
                        <a href="#" class="text-blue-600 font-semibold hover:text-blue-800 flex items-center gap-2 focus-soft">
                            En savoir plus <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Événement 3 -->
                <div class="event-card bg-white rounded-2xl overflow-hidden card-shadow relative card-border-soft">
                    <div class="event-date">
                        25 Fév 2025
                    </div>
                    <img
                        src="https://images.unsplash.com/photo-1503428593586-e225b39bddfe?auto=format&fit=crop&w=800&q=60"
                        alt="Journée portes ouvertes"
                        class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2 text-gray-800">Journée Portes Ouvertes</h3>
                        <p class="text-softer-gray mb-4">Découverte des services et rencontre avec notre équipe d'experts.</p>
                        <a href="#" class="text-blue-600 font-semibold hover:text-blue-800 flex items-center gap-2 focus-soft">
                            En savoir plus <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12">
                <button class="btn-primary text-white px-8 py-4 rounded-lg font-semibold text-lg focus-soft">
                    Voir tous les événements
                </button>
            </div>
        </div>
    </section> --}}

    <!-- CTA -->
    <section class="py-8 gradient-bg text-white fade-in">
        <div class="container mx-auto px-4 text-center">

            <h2 class="text-3xl md:text-4xl font-bold mb-6">Informez-vous sur vos droits</h2>

            <p class="mb-8 text-blue-100 text-lg max-w-3xl mx-auto leading-relaxed">
                Découvrez les informations essentielles pour préparer votre avenir en toute sérénité.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button class="btn-primary text-white px-8 py-4 rounded-lg font-semibold text-lg focus-soft">
                    En savoir plus
                </button>

                <button class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-50 focus-soft">
                    Prendre rendez-vous
                </button>
            </div>
        </div>
    </section>

    <!-- NEWSLETTER -->
    <section class="py-16 bg-white fade-in">
        <div class="container mx-auto px-6 max-w-4xl text-center">

            <h2 class="text-3xl md:text-4xl font-bold gradient-text mb-4">
                Abonnez-vous à notre Newsletter
            </h2>

            <p class="text-medium-gray text-lg mb-10">
                Recevez les dernières informations, annonces et mises à jour de la Direction de la Pension Civile.
            </p>

            <form method="POST" action="{{ route('newsletter.subscribe') }}" class="flex flex-col sm:flex-row gap-4 justify-center">
                @csrf

                <input type="email"
                    name="email"
                    required
                    placeholder="Entrez votre adresse email"
                    class="w-full sm:w-2/3 px-5 py-3 rounded-xl border border-gray-300 focus-soft shadow-sm">

                <button type="submit"
                        class="px-8 py-3 rounded-xl btn-primary shadow-md">
                    S’abonner
                </button>
            </form>

            @if (session('success'))
                <p class="text-green-600 mt-4 font-medium">{{ session('success') }}</p>
            @endif

            @if (session('error'))
                <p class="text-red-600 mt-4 font-medium">{{ session('error') }}</p>
            @endif

        </div>
    </section>

    <!-- QUICK LINKS -->
    <section class="py-8 bg-gray-50 fade-in">
        <div class="container mx-auto px-4">

            <h2 class="text-3xl md:text-4xl font-bold mb-12 text-center gradient-text">
                Accès Rapide
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Simulateur -->
                <div class="bg-blue-50 rounded-2xl p-6 text-center card-shadow hover-lift-soft card-border-soft">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calculator text-blue-600 text-xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4 text-gray-800">Simulateur</h3>

                    <p class="text-softer-gray mb-6">
                        Estimez votre future pension en quelques clics
                    </p>

                    <a href="{{ route('simulateur-calcul') }}" class="text-blue-600 font-semibold hover:text-blue-800 focus-soft">
                        Calculer ma pension →
                    </a>
                </div>

                <!-- Documents -->
                <div class="bg-green-50 rounded-2xl p-6 text-center card-shadow hover-lift-soft card-border-soft">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-alt text-green-600 text-xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4 text-gray-800">Documents légaux</h3>

                    <p class="text-softer-gray mb-6">
                        Accédez aux documents officiels
                    </p>

                    <a href="{{ route('textes_documents_legaux') }}" class="text-green-600 font-semibold hover:text-green-800 focus-soft">
                        Consulter les documents →
                    </a>
                </div>

                <!-- FAQ -->
                <div class="bg-purple-50 rounded-2xl p-6 text-center card-shadow hover-lift-soft card-border-soft">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-question-circle text-purple-600 text-xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4 text-gray-800">FAQ & Support</h3>

                    <p class="text-softer-gray mb-6">
                        Trouvez des réponses rapidement
                    </p>

                    <a href="{{ route('faq.index') }}" class="text-purple-600 font-semibold hover:text-purple-800 focus-soft">
                        Obtenir de l'aide →
                    </a>
                </div>

                <!-- Glossaire -->
                <div class="bg-yellow-50 rounded-2xl p-6 text-center card-shadow hover-lift-soft card-border-soft">
                    <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book text-yellow-600 text-xl"></i>
                    </div>

                    <h3 class="text-xl font-bold mb-4 text-gray-800">Glossaire</h3>

                    <p class="text-softer-gray mb-6">
                        Comprenez les termes clés
                    </p>

                    <a href="{{ route('glossaire') }}" class="text-yellow-600 font-semibold hover:text-yellow-800 focus-soft">
                        Consulter le glossaire →
                    </a>
                </div>

            </div>
        </div>
    </section>

{{--      <section class="py-8 bg-white fade-in">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold mb-8 text-center gradient-text">
                Nouveautés
            </h2>
            <x-nouveautes />
        </div>
    </section> --}}

<section class="py-12 bg-white fade-in">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold mb-8 text-center gradient-text">
            Actualités pour les Retraités
        </h2>

        @if($latestActualites->isEmpty())
            <p class="text-center text-gray-500 text-lg">
                Aucune actualité pour le moment. Revenez plus tard !
            </p>
        @else
            <div class="relative overflow-hidden">
                <div id="actualites-slider" class="flex gap-6 transition-transform duration-500">
                    @foreach($latestActualites as $actu)
                        <div class="min-w-full md:min-w-[30%] bg-gray-50 rounded-lg shadow p-6 flex-shrink-0">
                            @if($actu->image)
                                <img src="{{ asset('storage/' . $actu->image) }}" alt="{{ $actu->title }}" class="rounded mb-4 w-full h-40 object-cover">
                            @endif
                            <h3 class="text-xl font-semibold mb-2">{{ $actu->title }}</h3>
                            <p class="text-gray-700 mb-4">{{ Str::limit($actu->description, 80) }}</p>
                            <a href="{{ route('actualites.show', $actu->id) }}" class="text-blue-600 font-medium hover:underline">
                                Lire la suite
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>

@if(!$latestActualites->isEmpty())
<script>
    const slider = document.getElementById('actualites-slider');
    let scrollAmount = 0;

    function autoScroll() {
        scrollAmount += 1;
        if (scrollAmount >= slider.scrollWidth / 3) { // recommence à zéro après la dernière carte
            scrollAmount = 0;
        }
        slider.style.transform = `translateX(-${scrollAmount}px)`;
        requestAnimationFrame(autoScroll);
    }

    requestAnimationFrame(autoScroll);
</script>
@endif




    <section class="pt-8 bg-gray-50 fade-in">
        <div class="container mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold mb-8 text-center gradient-text">
                Informations utiles
            </h2>
            <x-mediatheque />
        </div>
    </section>

        <section class="pt-8 bg-gray-50 fade-in">
        <div class="container mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold mb-8 text-center gradient-text">
                Nos institutions partenaires
            </h2>
            <x-institutions-carousel speed="40"/>
        </div>
    </section>
</div>

        <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // === Bar Chart ===
        new Chart(document.getElementById('chartBar'), {
            type: 'bar',
            data: {
                labels: ['2020', '2021', '2022', '2023', '2024'],
                datasets: [{
                    label: 'Pensions traitées',
                    data: [3200, 4100, 3800, 4500, 4920],
                    backgroundColor: '#3b82f6'
                }]
            },
            options: { responsive: true }
        });

        // === Line Chart ===
        new Chart(document.getElementById('chartLine'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Demandes mensuelles',
                    data: [300, 420, 390, 500, 610, 580, 720],
                    borderColor: '#f59e0b',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false
                }]
            },
            options: { responsive: true }
        });

        // === Pie Chart ===
        new Chart(document.getElementById('chartPie'), {
            type: 'pie',
            data: {
                labels: ['Retraite', 'Réversion', 'Invalidité'],
                datasets: [{
                    label: 'Répartition',
                    data: [65, 25, 10],
                    backgroundColor: ['#10b981', '#3b82f6', '#9333ea']
                }]
            },
            options: { responsive: true }
        });
    </script>
@endsection
