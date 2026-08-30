@extends('layouts.main')

@section('title', 'Accueil')

@section('content')
<style>
    /* Amélioration des contrastes avec une approche plus douce */
    .gradient-bg {
        background: #0a4f86;
    }

    .gradient-text {
        color: #173052;
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
        from {
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

    @keyframes pulse {
        0%,
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
    @keyframes slide {
        0%,
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

    .hero-accueil {
        background: linear-gradient(180deg, #eef5fb 0%, #d9e6f2 60%, #9aafc6 100%);
        box-shadow: inset 0 -90px 70px -25px rgba(23, 48, 82, 0.28);
    }

    .hero-carousel .swiper-slide img.hero-accueil-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 40%;
        -webkit-mask-image: linear-gradient(to right, transparent 0%, #000 18%, #000 100%);
        mask-image: linear-gradient(to right, transparent 0%, #000 18%, #000 100%);
    }
</style>

<div class="py-0">
    <section class="py-0 gap-4 flex flex-col lg:flex-row lg:justify-between items-center bg-gray-100 w-full overflow-hidden">
        <x-carousel>
            <div class="swiper-slide">
                <div class="hero-accueil w-full h-full grid grid-cols-1 md:grid-cols-2 items-center">
                    <div class="px-6 sm:px-8 lg:px-10 py-6">
                        <h1 class="text-3xl md:text-4xl font-bold text-navy">
                            Pension Civile
                        </h1>
                        <p class="mt-2 text-lg md:text-xl font-semibold text-orange-500">
                            Votre retraite, notre engagement
                        </p>
                        <p class="mt-3 text-gray-500 text-sm md:text-base max-w-md leading-relaxed">
                            Nous accompagnons les fonctionnaires et retraités dans leurs démarches
                            liées à la retraite et aux prestations sociales.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm rounded-lg transition-colors">
                                Faire une démarche
                            </a>
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center px-5 py-2.5 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold text-sm rounded-xl transition-all duration-300">
                                Suivre mon dossier
                            </a>
                            <a href="{{ route('simulateur-calcul') }}"
                               class="inline-flex items-center justify-center px-5 py-2.5 bg-navy hover:bg-orange-500 text-white font-semibold text-sm rounded-lg transition-colors">
                                Calculer ma pension
                            </a>
                        </div>
                    </div>
                    <div class="relative hidden md:block h-full">
                        <img src="{{ asset('images/pension-hero-couple-salon.png') }}"
                             alt="Retraités utilisant les services de la Pension Civile"
                             class="hero-accueil-photo absolute inset-0">
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
                    $styleExtra = $slide->textStyleClasses();
                    $titleStyle = "color: {$textColor};";
                    $descStyle  = "color: {$textColor}; opacity: 0.85;";
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

    {{-- ═══════════════════════════════════════
         NOS SERVICES
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-gray-50 bg-motif fade-in">
        <div class="container mx-auto px-4">

            <div class="text-center mb-12">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Ce que nous offrons</span>
                <h2 class="text-3xl md:text-4xl font-bold text-navy mt-2 mb-3">Nos services</h2>
                <p class="text-gray-500 max-w-xl mx-auto">
                    Des démarches simplifiées pour les pensionnaires, fonctionnaires et institutions partenaires.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">

                {{-- Pensionnaire --}}
                <div class="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-[3px] bg-navy"></div>
                    <div class="p-7">
                        <div class="w-12 h-12 rounded-lg bg-navy text-white flex items-center justify-center mb-5">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3 class="text-lg font-bold text-navy mb-1">Pensionnaire</h3>
                        <p class="text-xs text-gray-400 mb-5">Gérez votre dossier de retraite en ligne</p>
                        <ul class="space-y-1.5">
                            @foreach([
                                ['Demande de pension de réversion', route('demandes.demande-pension-reversion.create')],
                                ['Enregistrement de pensionnaire', route('demandes.pension-pensionnaire.create')],
                                ["Demande d'arrêt de virement", route('demandes.demande-arret-virement.create')],
                                ['Attestation de pension', route('demandes.attestations.create')],
                            ] as [$item, $href])
                            <li>
                                <a href="{{ $href }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-slate-50 hover:text-navy transition-colors">
                                    {{ $item }}
                                    <i class="fas fa-arrow-right text-[10px] text-gray-300 group-hover:text-orange-500"></i>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Fonctionnaire --}}
                <div class="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-[3px] bg-navy"></div>
                    <div class="p-7">
                        <div class="w-12 h-12 rounded-lg bg-navy text-white flex items-center justify-center mb-5">
                            <i class="fas fa-id-badge"></i>
                        </div>
                        <h3 class="text-lg font-bold text-navy mb-1">Fonctionnaire</h3>
                        <p class="text-xs text-gray-400 mb-5">Préparez et suivez votre dossier de mise à la retraite</p>
                        <ul class="space-y-1.5">
                            @foreach([
                                ["Demande d'état de carrière", route('demandes.demande-etat-carriere.create')],
                                ['Demande de pension', route('demandes.demande-pension.index')],
                                ['Simulateur de pension', route('simulateur-calcul')],
                            ] as [$item, $href])
                            <li>
                                <a href="{{ $href }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-slate-50 hover:text-navy transition-colors">
                                    {{ $item }}
                                    <i class="fas fa-arrow-right text-[10px] text-gray-300 group-hover:text-orange-500"></i>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Institution --}}
                <div class="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-[3px] bg-navy"></div>
                    <div class="p-7">
                        <div class="w-12 h-12 rounded-lg bg-navy text-white flex items-center justify-center mb-5">
                            <i class="fas fa-building-columns"></i>
                        </div>
                        <h3 class="text-lg font-bold text-navy mb-1">Institution</h3>
                        <p class="text-xs text-gray-400 mb-5">Gérez vos démarches institutionnelles</p>
                        <ul class="space-y-1.5">
                            @foreach([
                                ["Demande d'adhésion", route('demandes.demande-adhesion.create')],
                                ['Transmission des demandes de pensions', route('demandes.demande-pension.index')],
                                ['Prendre rendez-vous', route('demandes.rencontre.create')],
                            ] as [$item, $href])
                            <li>
                                <a href="{{ $href }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-slate-50 hover:text-navy transition-colors">
                                    {{ $item }}
                                    <i class="fas fa-arrow-right text-[10px] text-gray-300 group-hover:text-orange-500"></i>
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
         PUBLICATIONS DES RAPPORTS
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-white bg-motif fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center mb-10">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Documents officiels</span>
                <h2 class="text-3xl md:text-4xl font-bold text-navy mt-2">Publications &amp; Rapports</h2>
                <p class="text-gray-500 text-sm max-w-xl mx-auto mt-3">
                    Rapports annuels, notes officielles et documents administratifs publiés par la Direction de la Pension Civile.
                </p>
                @if($recentReports->isNotEmpty())
                    <a href="{{ route('reports.index') }}"
                       class="inline-flex items-center justify-center mt-5 px-5 py-2.5 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold text-sm rounded-xl transition-all duration-300">
                        Plus de rapports
                    </a>
                @endif
            </div>

            @if($recentReports->isEmpty())
                <div class="text-center text-gray-400 py-10">
                    <i class="fas fa-folder-open text-3xl mb-3 block"></i>
                    Aucun rapport récent disponible.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($recentReports as $report)
                        <x-report-card :report="$report" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         NEWSLETTER
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-gray-50 bg-motif fade-in">
        <div class="container">
            <div class="bg-navy bg-motif-dark">
                <div class="px-8 py-12 md:px-12 md:py-14 text-center">
                    <span class="text-xs font-bold text-blue-200 uppercase tracking-widest">Restez informé</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-white mt-2 mb-3">
                        Abonnez-vous à notre newsletter
                    </h2>
                    <p class="text-blue-200 mb-7 max-w-lg mx-auto">
                        Recevez les dernières actualités, annonces officielles et mises à jour directement dans votre boîte mail.
                    </p>

                    <form method="POST" action="{{ route('newsletter.souscription') }}"
                          class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                            @csrf
                            <div class="flex-1">
                                <input type="email" name="email" required
                                       placeholder="votre@email.com"
                                       class="w-full px-4 py-3 rounded-xl bg-white border border-gray-200 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 text-sm">
                            </div>
                            <button type="submit"
                                    class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm rounded-lg transition-colors shrink-0">
                                S'abonner
                            </button>
                        </form>

                        @if(session('newsletter_success'))
                            <p class="text-green-300 text-sm mt-3 flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i> {{ session('newsletter_success') }}
                            </p>
                        @endif
                        @if(session('newsletter_error') || session('error'))
                            <p class="text-red-300 text-sm mt-3 flex items-center justify-center gap-2">
                                <i class="fas fa-exclamation-circle"></i> {{ session('newsletter_error') ?? session('error') }}
                            </p>
                        @endif
                        @if(session('success') && !session('newsletter_error'))
                            <p class="text-green-300 text-sm mt-3 flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                            </p>
                        @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         ACCÈS RAPIDE
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-white bg-motif fade-in">
        <div class="container mx-auto px-4">
            @php
                $accesRapide = [
                    [
                        'num'   => '01',
                        'icon'  => 'fa-calculator',
                        'title' => 'Simulateur de pension',
                        'desc'  => 'Estimez vos droits à la retraite à partir de votre carrière.',
                        'href'  => route('simulateur-calcul'),
                    ],
                    [
                        'num'   => '02',
                        'icon'  => 'fa-scale-balanced',
                        'title' => 'Textes légaux',
                        'desc'  => 'Décrets, lois et documents officiels encadrant la pension civile.',
                        'href'  => route('textes_documents_legaux'),
                    ],
                    [
                        'num'   => '03',
                        'icon'  => 'fa-circle-question',
                        'title' => 'Foire aux questions',
                        'desc'  => 'Réponses aux questions les plus fréquentes des usagers.',
                        'href'  => route('faq.index'),
                    ],
                    [
                        'num'   => '04',
                        'icon'  => 'fa-photo-film',
                        'title' => 'Médiathèque',
                        'desc'  => 'Photos, vidéos et documents de la Direction.',
                        'href'  => route('mediatheque'),
                    ],
                    [
                        'num'   => '05',
                        'icon'  => 'fa-book',
                        'title' => 'Glossaire',
                        'desc'  => 'Définitions des termes utilisés dans vos démarches.',
                        'href'  => route('glossaire'),
                    ],
                    [
                        'num'   => '06',
                        'icon'  => 'fa-envelope',
                        'title' => 'Nous contacter',
                        'desc'  => 'Adresse, horaires et formulaire de correspondance.',
                        'href'  => route('contact'),
                    ],
                ];
            @endphp

            <div class="text-center mb-10">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Services</span>
                <h2 class="text-3xl md:text-4xl font-bold text-navy mt-2">Accès rapide</h2>
                <p class="text-gray-500 text-sm max-w-xl mx-auto mt-3">
                    Les outils et ressources les plus consultés par les pensionnaires, fonctionnaires et institutions.
                </p>
            </div>

            <div class="rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="h-[3px] bg-[#173052]"></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-px bg-gray-100">
                        @foreach($accesRapide as $item)
                            <a href="{{ $item['href'] }}"
                               class="group relative flex flex-col bg-white p-6 md:p-7 hover:bg-slate-50 transition-colors duration-200">
                                <div class="flex items-start justify-between mb-5">
                                    <span class="w-11 h-11 rounded-lg bg-[#173052] text-white flex items-center justify-center group-hover:bg-orange-500 transition-colors duration-200">
                                        <i class="fas {{ $item['icon'] }} text-sm"></i>
                                    </span>
                                    <span class="text-xs font-bold text-gray-300 tracking-widest group-hover:text-blue-600 transition-colors">
                                        {{ $item['num'] }}
                                    </span>
                                </div>
                                <h3 class="text-[15px] font-bold text-gray-900 mb-1.5 group-hover:text-[#173052] transition-colors">
                                    {{ $item['title'] }}
                                </h3>
                                <p class="text-sm text-gray-500 leading-relaxed flex-1 mb-4">
                                    {{ $item['desc'] }}
                                </p>
                                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 group-hover:text-orange-500 transition-colors">
                                    Accéder
                                    <i class="fas fa-arrow-right text-[10px] transition-transform duration-200 group-hover:translate-x-1"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         ACTUALITÉS ET PUBLICATIONS
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-gray-50 bg-motif fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center mb-10">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Dernières nouvelles</span>
                <h2 class="text-3xl md:text-4xl font-bold text-navy mt-2">Actualités</h2>
                <p class="text-gray-500 text-sm max-w-xl mx-auto mt-3">
                    Les dernières nouvelles concernant les actions et services de la Direction de la Pension Civile.
                </p>
                @if($latestActualites->isNotEmpty())
                    <a href="{{ route('actualites.index') }}"
                       class="inline-flex items-center justify-center mt-5 px-5 py-2.5 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold text-sm rounded-xl transition-all duration-300">
                        Plus d'actualités
                    </a>
                @endif
            </div>

            @if($latestActualites->isEmpty())
                <div class="text-center text-gray-400 py-10">
                    <i class="fas fa-newspaper text-3xl mb-3 block"></i>
                    Aucune actualité disponible pour le moment.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($latestActualites as $actu)
                        <x-actualite-card :actualite="$actu" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         NOS PARTENAIRES
    ════════════════════════════════════════ --}}
    <section class="py-12 bg-white bg-motif fade-in">
        <div class="container mx-auto px-4">
            <div class="text-center mb-8">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Réseau institutionnel</span>
                <h2 class="text-3xl md:text-4xl font-bold text-navy mt-2">Nos institutions partenaires</h2>
            </div>
            <x-institutions-carousel speed="40" />
        </div>
    </section>

</div>
@endsection
