@extends('layouts.main')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
@endpush

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

    <blade keyframes|%20fadeIn%20%7B>from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
    }

    .pulse-slow {
        animation: pulse 3s infinite;
    }

    <blade keyframes|%20pulse%20%7B>0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.02);
    }
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
    <blade keyframes|%20slide%20%7B>0%,
    20% {
        transform: translateX(0);
    }

    25%,
    45% {
        transform: translateX(-100%);
    }

    50%,
    70% {
        transform: translateX(-200%);
    }

    75%,
    95% {
        transform: translateX(-300%);
    }

    100% {
        transform: translateX(-400%);
    }
    }

    .auto-carousel {
        animation: slide 25s infinite;
    }

    .auto-carousel:hover {
        animation-play-state: paused;
    }
</style>

<div class="py-0">
    <section class="py-0 gap-4 flex flex-col lg:flex-row lg:justify-between items-center bg-gray-100 w-full overflow-hidden">
        <x-carousel>
            <!-- Slide 1 -->
            <div class="swiper-slide">
                <div class="gradient-bg w-full h-full flex items-center justify-center">
                    <div class="carousel-text-content fade-in">
                        <h1 class="text-2xl sm:text-4xl md:text-6xl font-bold mb-4 md:mb-6 text-white">Direction de la Pension Civile</h1>
                        <p class="text-base sm:text-xl md:text-2xl text-blue-100 mb-5 md:mb-8 max-w-3xl mx-auto">
                            Votre partenaire de confiance pour une retraite sereine et sécurisée
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('simulateur-calcul') }}" class="focus-soft">
                                <button
                                    class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-colors">
                                    Calculer ma pension
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($carousels as $slide)
                @php
                    $pos = $slide->overlay_position ?? 'bottom-left';
                    $gradientClass = match(true) {
                        str_starts_with($pos, 'bottom') => 'bg-gradient-to-t from-black/65 via-black/20 to-transparent',
                        str_starts_with($pos, 'top')    => 'bg-gradient-to-b from-black/65 via-black/20 to-transparent',
                        default                         => 'bg-black/35',
                    };
                    $alignClass = match($pos) {
                        'bottom-left'   => 'items-end justify-start text-left',
                        'bottom-center' => 'items-end justify-center text-center',
                        'bottom-right'  => 'items-end justify-end text-right',
                        'center'        => 'items-center justify-center text-center',
                        'top-left'      => 'items-start justify-start text-left',
                        'top-center'    => 'items-start justify-center text-center',
                        default         => 'items-end justify-start text-left',
                    };
                    $size = $slide->text_size ?? 'md';
                    [$titleClass, $descClass] = match($size) {
                        'sm' => ['text-base sm:text-lg md:text-xl',        'text-xs sm:text-sm'],
                        'md' => ['text-xl sm:text-3xl md:text-4xl',        'text-sm sm:text-base md:text-lg'],
                        'lg' => ['text-2xl sm:text-4xl md:text-5xl',       'text-base sm:text-lg md:text-xl'],
                        'xl' => ['text-3xl sm:text-5xl md:text-6xl',       'text-lg sm:text-xl md:text-2xl'],
                        default => ['text-xl sm:text-3xl md:text-4xl',     'text-sm sm:text-base md:text-lg'],
                    };
                    $textColor  = $slide->text_color ?? '#ffffff';
                    $textStyles = $slide->text_styles ?? [];
                    $fontCss    = \App\Models\Carousel::FONT_CSS[$slide->font_family ?? 'sans'] ?? \App\Models\Carousel::FONT_CSS['sans'];
                    $styleExtra = $slide->textStyleClasses();
                    $titleStyle = "color: {$textColor}; font-family: {$fontCss};";
                    $descStyle  = "color: {$textColor}; font-family: {$fontCss}; opacity: 0.85;";
                    $hasOverlay = $slide->title || $slide->description || $slide->cta_label;
                @endphp
                <div class="swiper-slide">
                    <img src="{{ $slide->imageUrl() }}"
                         alt="{{ $slide->title ?? 'Direction de la Pension Civile' }}"
                         loading="lazy">

                    @if($hasOverlay)
                        {{-- Gradient overlay --}}
                        <div class="absolute inset-0 {{ $gradientClass }} pointer-events-none"></div>

                        {{-- Text overlay --}}
                        <div class="absolute inset-0 flex p-6 sm:p-10 {{ $alignClass }}">
                            <div class="max-w-xl">
                                @if($slide->title)
                                    <h2 class="{{ $titleClass }} {{ $styleExtra }} leading-tight drop-shadow-lg mb-2"
                                        style="{{ $titleStyle }}">
                                        {{ $slide->title }}
                                    </h2>
                                @endif
                                @if($slide->description)
                                    <p class="{{ $descClass }} leading-relaxed drop-shadow mb-4"
                                       style="{{ $descStyle }}">
                                        {{ $slide->description }}
                                    </p>
                                @endif
                                @if($slide->cta_label && $slide->link)
                                    <a href="{{ $slide->link }}"
                                       class="inline-flex items-center gap-2 bg-white text-gray-900 font-semibold text-sm px-5 py-2.5 rounded-full shadow-lg hover:bg-blue-50 transition-colors">
                                        {{ $slide->cta_label }}
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </a>
                                @elseif($slide->cta_label)
                                    <span class="inline-flex items-center gap-2 bg-white text-gray-900 font-semibold text-sm px-5 py-2.5 rounded-full shadow-lg">
                                        {{ $slide->cta_label }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </x-carousel>
        <div class="w-full lg:w-auto mt-0 lg:mt-0 px-4 lg:px-0">
            <x-presentation slug="ministre" />
        </div>
    </section>

    <section class="py-8 bg-gray-50 fade-in">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <!-- Image -->
                <div class="relative w-full lg:w-1/2">
                    <div class="rounded-2xl overflow-hidden">
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
                        La Direction de la Pension Civile accompagne les fonctionnaires et retraités dans toutes leurs
                        démarches.
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

    <section class="py-8 gradient-bg text-white fade-in">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold mb-12 text-center">Notre vision et notre mission</h2>
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Vision -->
                <div class="bg-white rounded-2xl p-8 card-shadow hover-lift-soft card-border-soft">
                    <div class="flex justify-center">
                        <div
                            class="w-20 h-20 bg-blue-50 rounded-xl flex items-center justify-center mb-4 text-blue-600 text-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye">
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                                <circle cx="12" cy="12" r="3" /></svg>
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
                        <div
                            class="w-20 h-20 bg-green-50 rounded-xl flex items-center justify-center mb-4 text-green-600 text-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-target-icon lucide-target">
                                <circle cx="12" cy="12" r="10" />
                                <circle cx="12" cy="12" r="6" />
                                <circle cx="12" cy="12" r="2" /></svg>
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
                <div
                    class="flex flex-col items-center justify-center bg-white/10 rounded-2xl py-8 backdrop-blur-sm border border-white/20">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mb-6 pulse-slow">
                        <i class="fas fa-play text-orange-500 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4 text-center">Découvrez notre institution</h3>
                    <p class="text-blue-100 text-center mb-6">
                        Visionnez notre vidéo de présentation
                    </p>
                    <div class="rounded-lg">
                        <x-video-card videoUrl="https://www.youtube.com/watch?v=8sigu4fUheo" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         NOS SERVICES
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-white fade-in">
        <div class="container mx-auto px-4">

            <div class="text-center mb-12">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Ce que nous offrons</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2 mb-3">Nos Services</h2>
                <p class="text-gray-500 max-w-xl mx-auto">
                    Des démarches simplifiées pour les pensionnaires, fonctionnaires et institutions partenaires.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">

                {{-- Pensionnaire --}}
                <div class="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-blue-700"></div>
                    <div class="p-7">
                        <div class="w-13 h-13 w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mb-5">
                            <i class="fas fa-user-tie text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Pensionnaire</h3>
                        <p class="text-xs text-gray-400 mb-5">Gérez votre dossier de retraite en ligne</p>
                        <ul class="space-y-1.5">
                            @foreach([
                                'Demande de pension de réversion',
                                'Enregistrement de pensionnaire',
                                "Demande d'arrêt de virement",
                                'Attestation de pension',
                            ] as $item)
                            <li>
                                <a href="#" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                    {{ $item }}
                                    <i class="fas fa-arrow-right text-[10px] text-gray-300 group-hover:text-blue-400"></i>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Fonctionnaire --}}
                <div class="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-700"></div>
                    <div class="p-7">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-5">
                            <i class="fas fa-id-badge text-emerald-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Fonctionnaire</h3>
                        <p class="text-xs text-gray-400 mb-5">Préparez et suivez votre dossier de mise à la retraite</p>
                        <ul class="space-y-1.5">
                            @foreach([
                                "Demande d'état de carrière",
                                'Demande de pension',
                                'Relevé de service',
                            ] as $item)
                            <li>
                                <a href="#" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                                    {{ $item }}
                                    <i class="fas fa-arrow-right text-[10px] text-gray-300"></i>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Institution --}}
                <div class="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-500 to-violet-700"></div>
                    <div class="p-7">
                        <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center mb-5">
                            <i class="fas fa-building-columns text-violet-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Institution</h3>
                        <p class="text-xs text-gray-400 mb-5">Gérez vos démarches institutionnelles</p>
                        <ul class="space-y-1.5">
                            @foreach([
                                "Demande d'adhésion",
                                'Transmission des demandes de pensions',
                                'Suivi des dossiers',
                            ] as $item)
                            <li>
                                <a href="#" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-violet-50 hover:text-violet-700 transition-colors">
                                    {{ $item }}
                                    <i class="fas fa-arrow-right text-[10px] text-gray-300"></i>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         NOTRE INSTITUTION EN IMAGES
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-gray-50 fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center mb-10">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Galerie</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2">Notre Institution en Images</h2>
            </div>
            <x-auto-slide-carousel />
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         CTA — INFORMEZ-VOUS
    ════════════════════════════════════════ --}}
    <section class="py-14 gradient-bg fade-in">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-10 md:gap-16">
                <div class="flex-1 text-white">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 leading-tight">
                        Connaissez-vous tous vos droits à la retraite ?
                    </h2>
                    <p class="text-blue-100 text-lg leading-relaxed mb-6">
                        La Direction de la Pension Civile met à votre disposition toutes les informations essentielles pour préparer votre avenir en toute sérénité.
                    </p>
                    <ul class="space-y-3">
                        @foreach([
                            'Calcul de votre pension civile',
                            'Conditions et délais de traitement',
                            'Documents requis pour chaque démarche',
                        ] as $point)
                        <li class="flex items-center gap-3 text-blue-100">
                            <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                                <i class="fas fa-check text-white text-[9px]"></i>
                            </span>
                            {{ $point }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="shrink-0 flex flex-col gap-3 w-full md:w-auto">
                    <a href="{{ route('simulateur-calcul') }}"
                       class="flex items-center justify-center gap-3 px-8 py-4 bg-white text-blue-700 font-bold rounded-xl shadow-lg hover:bg-blue-50 transition-colors text-center">
                        <i class="fas fa-calculator"></i> Simuler ma pension
                    </a>
                    <a href="{{ route('demandes.rencontre.create') }}"
                       class="flex items-center justify-center gap-3 px-8 py-4 bg-white/10 border border-white/30 text-white font-semibold rounded-xl hover:bg-white/20 transition-colors text-center backdrop-blur-sm">
                        <i class="fas fa-calendar-check"></i> Prendre rendez-vous
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         PUBLICATIONS DES RAPPORTS
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-white fade-in">
        <div class="container mx-auto px-4">

            <div class="flex items-end justify-between mb-10">
                <div>
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Documents officiels</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-1">Publications & Rapports</h2>
                </div>
                <a href="{{ route('reports.index') }}"
                   class="hidden md:inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                    Voir tous les rapports <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            @if($recentReports->isEmpty())
                <div class="text-center py-10 text-gray-400">
                    <i class="fas fa-folder-open text-3xl mb-3 block"></i>
                    Aucun rapport récent disponible.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($recentReports as $report)
                        <div class="group flex items-start gap-5 bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-blue-200 rounded-2xl p-5 transition-all duration-300">
                            {{-- Icon --}}
                            <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                <i class="fas fa-file-pdf text-lg"></i>
                            </div>
                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-blue-800 transition-colors">
                                        {{ $report->title }}
                                    </h4>
                                    @if($report->year)
                                        <span class="text-xs font-semibold px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">
                                            {{ $report->year }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 line-clamp-1">
                                    {{ $report->description ?: 'Document officiel de la Direction de la Pension Civile.' }}
                                </p>
                                @if($report->published_at)
                                    <p class="text-xs text-gray-400 mt-1">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        {{ $report->published_at->translatedFormat('d F Y') }}
                                    </p>
                                @endif
                            </div>
                            {{-- Actions --}}
                            <div class="shrink-0 flex items-center gap-2">
                                <a href="{{ route('reports.show', $report) }}"
                                   class="text-xs font-semibold px-3 py-2 bg-white border border-gray-200 hover:border-blue-300 text-gray-700 hover:text-blue-700 rounded-lg transition-colors">
                                    <i class="fas fa-eye mr-1"></i> Consulter
                                </a>
                                <a href="{{ route('reports.download', $report) }}"
                                   class="text-xs font-semibold px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                    <i class="fas fa-download mr-1"></i> Télécharger
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 text-center md:hidden">
                    <a href="{{ route('reports.index') }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                        Voir tous les rapports <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            @endif

        </div>
    </section>

    {{-- ═══════════════════════════════════════
         NEWSLETTER
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-gray-50 fade-in">
        <div class="container mx-auto px-4">
            <div class="bg-gradient-to-br from-blue-900 to-blue-700 rounded-3xl overflow-hidden">
                <div class="flex flex-col md:flex-row items-center gap-0">
                    {{-- Left decoration --}}
                    <div class="hidden md:flex w-64 shrink-0 items-center justify-center p-10 opacity-20">
                        <i class="fas fa-envelope-open-text text-white" style="font-size: 8rem;"></i>
                    </div>
                    {{-- Content --}}
                    <div class="flex-1 p-8 md:p-12">
                        <span class="text-xs font-bold text-blue-300 uppercase tracking-widest">Restez informé</span>
                        <h2 class="text-2xl md:text-3xl font-bold text-white mt-2 mb-3">
                            Abonnez-vous à notre Newsletter
                        </h2>
                        <p class="text-blue-200 mb-7 max-w-lg">
                            Recevez les dernières actualités, annonces officielles et mises à jour directement dans votre boîte mail.
                        </p>

                        <form method="POST" action="{{ route('newsletter.souscription') }}"
                              class="flex flex-col sm:flex-row gap-3 max-w-md">
                            @csrf
                            <div class="flex-1">
                                <input type="email" name="email" required
                                       placeholder="votre@email.com"
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-white/50 text-sm">
                            </div>
                            <button type="submit"
                                    class="px-6 py-3 bg-white text-blue-800 font-bold rounded-xl hover:bg-blue-50 transition-colors text-sm shrink-0">
                                S'abonner
                            </button>
                        </form>

                        @if(session('newsletter_success'))
                            <p class="text-green-300 text-sm mt-3 flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> {{ session('newsletter_success') }}
                            </p>
                        @endif
                        @if(session('newsletter_error') || session('error'))
                            <p class="text-red-300 text-sm mt-3 flex items-center gap-2">
                                <i class="fas fa-exclamation-circle"></i> {{ session('newsletter_error') ?? session('error') }}
                            </p>
                        @endif
                        @if(session('success') && !session('newsletter_error'))
                            <p class="text-green-300 text-sm mt-3 flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         ACCÈS RAPIDE
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-white fade-in">
        <div class="container mx-auto px-4">

            <div class="text-center mb-10">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Navigation</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2">Accès Rapide</h2>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                <a href="{{ route('simulateur-calcul') }}"
                   class="group flex flex-col items-center text-center bg-blue-50 hover:bg-blue-600 border border-blue-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="w-14 h-14 bg-blue-100 group-hover:bg-white/20 rounded-2xl flex items-center justify-center mb-4 transition-colors">
                        <i class="fas fa-calculator text-blue-600 group-hover:text-white text-xl transition-colors"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 group-hover:text-white text-sm mb-1 transition-colors">Simulateur</h3>
                    <p class="text-xs text-gray-500 group-hover:text-blue-100 transition-colors">Estimez votre pension</p>
                </a>

                <a href="{{ route('textes_documents_legaux') }}"
                   class="group flex flex-col items-center text-center bg-emerald-50 hover:bg-emerald-600 border border-emerald-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="w-14 h-14 bg-emerald-100 group-hover:bg-white/20 rounded-2xl flex items-center justify-center mb-4 transition-colors">
                        <i class="fas fa-gavel text-emerald-600 group-hover:text-white text-xl transition-colors"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 group-hover:text-white text-sm mb-1 transition-colors">Textes légaux</h3>
                    <p class="text-xs text-gray-500 group-hover:text-emerald-100 transition-colors">Documents officiels</p>
                </a>

                <a href="{{ route('faq.index') }}"
                   class="group flex flex-col items-center text-center bg-violet-50 hover:bg-violet-600 border border-violet-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="w-14 h-14 bg-violet-100 group-hover:bg-white/20 rounded-2xl flex items-center justify-center mb-4 transition-colors">
                        <i class="fas fa-circle-question text-violet-600 group-hover:text-white text-xl transition-colors"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 group-hover:text-white text-sm mb-1 transition-colors">FAQ</h3>
                    <p class="text-xs text-gray-500 group-hover:text-violet-100 transition-colors">Réponses rapides</p>
                </a>

                <a href="{{ route('mediatheque') }}"
                   class="group flex flex-col items-center text-center bg-amber-50 hover:bg-amber-500 border border-amber-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="w-14 h-14 bg-amber-100 group-hover:bg-white/20 rounded-2xl flex items-center justify-center mb-4 transition-colors">
                        <i class="fas fa-photo-film text-amber-600 group-hover:text-white text-xl transition-colors"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 group-hover:text-white text-sm mb-1 transition-colors">Médiathèque</h3>
                    <p class="text-xs text-gray-500 group-hover:text-amber-100 transition-colors">Vidéos & documents</p>
                </a>

            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         ACTUALITÉS POUR LES RETRAITÉS
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-gray-50 fade-in">
        <div class="container mx-auto px-4">

            <div class="flex items-end justify-between mb-10">
                <div>
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Dernières nouvelles</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-1">Actualités pour les Retraités</h2>
                </div>
                @if($latestActualites->count() > 0)
                    <a href="{{ route('actualites.index') }}"
                       class="hidden md:inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                        Toutes les actualités <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                @endif
            </div>

            @if($latestActualites->isEmpty())
                <div class="text-center py-10 text-gray-400">
                    <i class="fas fa-newspaper text-3xl mb-3 block"></i>
                    Aucune actualité disponible pour le moment.
                </div>
            @else
                @php $items = $latestActualites; $first = $items->first(); @endphp

                <div class="grid lg:grid-cols-3 gap-6">

                    {{-- Featured article --}}
                    <a href="{{ route('actualites.show', $first->id) }}"
                       class="group lg:col-span-2 block bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300">
                        <div class="relative overflow-hidden" style="aspect-ratio: 16/8;">
                            <img src="{{ $first->images->isNotEmpty() ? Storage::url($first->images->first()->image_path) : asset('images/image_placeholder.png') }}"
                                 alt="{{ $first->title }}"
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 absolute inset-0">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            @if($first->category)
                                <span class="absolute top-4 left-4 px-2.5 py-1 bg-blue-600 text-white text-xs font-bold rounded-full">
                                    {{ $first->category }}
                                </span>
                            @endif
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <p class="text-blue-300 text-xs mb-2">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    {{ $first->created_at->translatedFormat('d F Y') }}
                                </p>
                                <h3 class="text-white font-bold text-lg md:text-xl leading-tight line-clamp-2 group-hover:text-blue-200 transition-colors">
                                    {{ $first->title }}
                                </h3>
                            </div>
                        </div>
                    </a>

                    {{-- Side articles --}}
                    <div class="flex flex-col gap-4">
                        @foreach($items->slice(1)->take(3) as $actu)
                            <a href="{{ route('actualites.show', $actu->id) }}"
                               class="group flex gap-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md p-4 transition-all duration-300 hover:-translate-y-0.5">
                                <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                                    <img src="{{ $actu->images->isNotEmpty() ? Storage::url($actu->images->first()->image_path) : asset('images/image_placeholder.png') }}"
                                         alt="{{ $actu->title }}"
                                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                                </div>
                                <div class="flex-1 min-w-0">
                                    @if($actu->category)
                                        <span class="text-xs font-semibold text-blue-600">{{ $actu->category }}</span>
                                    @endif
                                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition-colors line-clamp-2 leading-snug mt-0.5">
                                        {{ $actu->title }}
                                    </h4>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $actu->created_at->translatedFormat('d M Y') }}
                                    </p>
                                </div>
                            </a>
                        @endforeach

                        <a href="{{ route('actualites.index') }}"
                           class="inline-flex items-center justify-center gap-2 px-4 py-3 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold text-sm rounded-xl transition-all duration-300 mt-auto">
                            Toutes les actualités <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>

                </div>
            @endif

        </div>
    </section>

    {{-- ═══════════════════════════════════════
         INFORMATIONS UTILES (MÉDIATHÈQUE)
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-white fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center mb-10">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Ressources</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2">Informations utiles</h2>
            </div>
            <x-mediatheque />
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         NOS PARTENAIRES
    ════════════════════════════════════════ --}}
    <section class="py-12 bg-gray-50 fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center mb-8">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Réseau institutionnel</span>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-2">Nos institutions partenaires</h2>
            </div>
            <x-institutions-carousel speed="40" />
        </div>
    </section>

</div>
@endsection
