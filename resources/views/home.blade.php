@extends('layouts.main')

@section('title', __('messages.home'))

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

    .home-band {
        border-top: 1px solid #e5e7eb;
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
    <section class="w-full">
        <x-carousel>
            <div class="swiper-slide">
                <div class="hero-accueil w-full h-full grid grid-cols-1 md:grid-cols-2 items-center">
                    <div class="px-6 sm:px-8 lg:px-12 py-6">
                        <h1 class="text-3xl md:text-4xl font-bold text-navy">
                            {{ __('home.hero_title') }}
                        </h1>
                        <p class="mt-2 text-lg md:text-xl font-semibold text-orange-500">
                            {{ __('home.hero_tagline') }}
                        </p>
                        <p class="mt-3 text-gray-500 text-sm md:text-base max-w-md leading-relaxed">
                            {{ __('home.hero_body') }}
                        </p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <!-- <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm rounded-lg transition-colors">
                                {{ __('home.hero_cta_demarche') }}
                            </a>
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center px-5 py-2.5 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold text-sm rounded-xl transition-all duration-300">
                                {{ __('home.hero_cta_suivi') }}
                            </a> -->
                            <a href="{{ route('simulateur-calcul') }}"
                               class="inline-flex items-center justify-center px-5 py-2.5 bg-navy hover:bg-orange-500 text-white font-semibold text-sm rounded-lg transition-colors">
                                {{ __('home.hero_cta_calcul') }}
                            </a>
                        </div>
                    </div>
                    <div class="relative hidden md:block h-full">
                        <img src="{{ asset('images/pension-hero-couple-salon.png') }}"
                             alt="{{ __('home.hero_photo_alt') }}"
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
    </section>

    {{-- ═══════════════════════════════════════
         VISION ET MISSION
    ════════════════════════════════════════ --}}
    @php
        $videoItem = \App\Models\MediathequeItem::published()->featured()->where('type', 'video')->first()
            ?? \App\Models\MediathequeItem::published()->where('type', 'video')->ordered()->first();
        $videoTitle = $videoItem?->title ?? 'Jounen enfòmasyon ak oryantasyon';
        $videoEmbed = $videoItem?->embedUrl();
        $videoSrc = $videoItem?->fileUrl();
        if (! $videoSrc && $videoItem?->url && preg_match('/\.(mp4|webm|ogg)(\?|$)/i', $videoItem->url)) {
            $videoSrc = $videoItem->url;
        }
    @endphp
    <section class="py-16 bg-slate-100 bg-motif fade-in home-band">
        <div class="container mx-auto px-4">
            <div class="grid lg:grid-cols-12 gap-8 items-start">
                <aside class="lg:col-span-3 max-w-sm mx-auto lg:max-w-none lg:mx-0">
                    <x-presentation slug="ministre" variant="card" />
                </aside>
                <div class="lg:col-span-9">
                    <h2 class="text-3xl md:text-4xl font-bold text-navy text-center mb-10">
                        {{ __('home.vision_mission_title') }}
                    </h2>
                    <div class="grid md:grid-cols-3 gap-5 items-stretch">
                        <article class="bg-white rounded-2xl p-6 md:p-7">
                            <h3 class="text-lg font-bold text-navy mb-2">{{ __('home.vision_title') }}</h3>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                {{ __('home.vision_body') }}
                            </p>
                        </article>

                        <article class="bg-white rounded-2xl p-6 md:p-7">
                            <h3 class="text-lg font-bold text-navy mb-3">{{ __('home.mission_title') }}</h3>
                            <ul class="space-y-2.5 text-sm text-gray-600">
                                @foreach(['home.mission_1', 'home.mission_2', 'home.mission_3', 'home.mission_4'] as $point)
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check text-emerald-500 mt-0.5 text-xs"></i>
                                        <span>{{ __($point) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </article>

                        <article class="bg-[#1e4a7a] rounded-2xl p-6 md:p-7 flex flex-col">
                            <h3 class="text-lg font-bold text-white mb-1">{{ __('home.video_title') }}</h3>
                            <p class="text-sm text-blue-100 mb-4">{{ __('home.video_subtitle') }}</p>
                            @if($videoSrc)
                                <video
                                    class="w-full aspect-video rounded-xl bg-navy mt-auto"
                                    controls
                                    playsinline
                                    preload="metadata"
                                >
                                    <source src="{{ $videoSrc }}">
                                    {{ $videoTitle }}
                                </video>
                            @elseif($videoEmbed)
                                <div class="relative w-full aspect-video rounded-xl overflow-hidden bg-navy mt-auto">
                                    <iframe
                                        class="absolute inset-0 w-full h-full"
                                        src="{{ $videoEmbed }}"
                                        title="{{ $videoTitle }}"
                                        loading="lazy"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen
                                        style="border:0;"
                                    ></iframe>
                                </div>
                            @endif
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         NOS SERVICES
    ════════════════════════════════════════ --}}
    <section class="py-16 bg-white bg-motif fade-in home-band">
        <div class="container mx-auto px-4">
                    <div class="text-center mb-10">
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">{{ __('home.services_kicker') }}</span>
                        <h2 class="text-3xl md:text-4xl font-bold text-navy mt-2 mb-3">{{ __('home.services_title') }}</h2>
                        <p class="text-gray-500 max-w-xl mx-auto">
                            {{ __('home.services_intro') }}
                        </p>
                    </div>
                    @php
                        $serviceCards = [
                            [
                                'icon' => 'fa-user-tie',
                                'audience' => __('home.service_pensionnaire_audience'),
                                'title' => __('home.service_pensionnaire_title'),
                                'desc' => __('home.service_pensionnaire_desc'),
                                'items' => [
                                    [__('home.service_pensionnaire_reversion'), route('demandes.demande-pension-reversion.create')],
                                    [__('home.service_pensionnaire_enregistrement'), route('demandes.pension-pensionnaire.create')],
                                    [__('home.service_pensionnaire_arret_virement'), route('demandes.demande-arret-virement.create')],
                                    [__('home.service_pensionnaire_attestation'), route('demandes.attestations.create')],
                                ],
                            ],
                            [
                                'icon' => 'fa-id-badge',
                                'audience' => __('home.service_fonctionnaire_audience'),
                                'title' => __('home.service_fonctionnaire_title'),
                                'desc' => __('home.service_fonctionnaire_desc'),
                                'items' => [
                                    [__('home.service_fonctionnaire_carriere'), route('demandes.demande-etat-carriere.create')],
                                    [__('home.service_fonctionnaire_pension'), route('demandes.demande-pension.index')],
                                    [__('home.service_fonctionnaire_simulateur'), route('simulateur-calcul')],
                                ],
                            ],
                            [
                                'icon' => 'fa-building-columns',
                                'audience' => __('home.service_institution_audience'),
                                'title' => __('home.service_institution_title'),
                                'desc' => __('home.service_institution_desc'),
                                'items' => [
                                    [__('home.service_institution_adhesion'), route('demandes.demande-adhesion.create')],
                                    [__('home.service_institution_transmission'), route('demandes.demande-pension.index')],
                                    [__('home.service_institution_rdv'), route('demandes.rencontre.create')],
                                ],
                            ],
                        ];
                    @endphp
                    <div class="grid md:grid-cols-3 gap-6 items-stretch">
                        @foreach($serviceCards as $card)
                            <article class="flex flex-col bg-white rounded-2xl border border-slate-200/80 shadow-[0_8px_28px_rgba(23,48,82,0.06)] overflow-hidden hover:-translate-y-1 hover:shadow-[0_16px_36px_rgba(23,48,82,0.12)] transition-all duration-300">
                                <div class="bg-navy bg-motif-dark px-7 py-6 text-white">
                                    <div class="w-11 h-11 rounded-lg bg-white/10 text-white flex items-center justify-center mb-4">
                                        <i class="fas {{ $card['icon'] }}"></i>
                                    </div>
                                    <p class="text-[11px] font-bold text-blue-200 uppercase tracking-widest">{{ $card['audience'] }}</p>
                                    <h3 class="text-xl font-bold mt-1">{{ $card['title'] }}</h3>
                                    <p class="text-sm text-blue-100/90 mt-1.5 leading-relaxed">{{ $card['desc'] }}</p>
                                </div>
                                <ul class="flex-1 p-4 space-y-1">
                                    @foreach($card['items'] as [$item, $href])
                                        <li>
                                            <a href="{{ $href }}"
                                               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-slate-50 hover:text-navy transition-colors">
                                                <span class="w-1.5 h-1.5 rounded-full bg-orange-400 shrink-0"></span>
                                                <span class="flex-1 leading-snug">{{ $item }}</span>
                                                <i class="fas fa-arrow-right text-[10px] text-gray-300 group-hover:text-orange-500 group-hover:translate-x-0.5 transition-all"></i>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
        </div>
    </section>


    {{-- ═══════════════════════════════════════
         ACCÈS RAPIDE
    ════════════════════════════════════════ --}}
    <section class="py-16 bg-slate-100 bg-motif fade-in home-band">
        <div class="container mx-auto px-4">
            @php
                $accesRapide = [
                    ['num' => '01', 'icon' => 'fa-calculator', 'title' => __('home.acces_simulateur_title'), 'desc' => __('home.acces_simulateur_desc'), 'href' => route('simulateur-calcul')],
                    ['num' => '02', 'icon' => 'fa-scale-balanced', 'title' => __('home.acces_textes_title'), 'desc' => __('home.acces_textes_desc'), 'href' => route('textes_documents_legaux')],
                    ['num' => '03', 'icon' => 'fa-circle-question', 'title' => __('home.acces_faq_title'), 'desc' => __('home.acces_faq_desc'), 'href' => route('faq.index')],
                    ['num' => '04', 'icon' => 'fa-photo-film', 'title' => __('home.acces_media_title'), 'desc' => __('home.acces_media_desc'), 'href' => route('mediatheque')],
                    ['num' => '05', 'icon' => 'fa-book', 'title' => __('home.acces_glossaire_title'), 'desc' => __('home.acces_glossaire_desc'), 'href' => route('glossaire')],
                    ['num' => '06', 'icon' => 'fa-envelope', 'title' => __('home.acces_contact_title'), 'desc' => __('home.acces_contact_desc'), 'href' => route('contact')],
                ];
            @endphp
            <div class="text-center mb-12">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">{{ __('home.acces_kicker') }}</span>
                <h2 class="text-3xl md:text-4xl font-bold text-navy mt-2">{{ __('home.acces_title') }}</h2>
                <p class="text-gray-500 text-sm max-w-xl mx-auto mt-3">
                    {{ __('home.acces_intro') }}
                </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($accesRapide as $item)
                    <a href="{{ $item['href'] }}"
                       class="group flex flex-col rounded-2xl border border-slate-200/80 bg-white p-6 md:p-7 shadow-[0_6px_24px_rgba(23,48,82,0.05)] hover:-translate-y-1 hover:border-navy/15 hover:shadow-[0_16px_36px_rgba(23,48,82,0.11)] transition-all duration-300">
                        <div class="flex items-start justify-between mb-5">
                            <span class="w-12 h-12 rounded-xl bg-navy text-white flex items-center justify-center group-hover:bg-orange-500 transition-colors duration-200">
                                <i class="fas {{ $item['icon'] }}"></i>
                            </span>
                            <span class="text-2xl font-bold text-slate-100 leading-none group-hover:text-orange-100 transition-colors">
                                {{ $item['num'] }}
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-navy">{{ $item['title'] }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed flex-1 mt-2 mb-5">{{ $item['desc'] }}</p>
                        <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 group-hover:text-orange-500 transition-colors">
                            {{ __('home.acces_cta') }}
                            <i class="fas fa-arrow-right text-[10px] transition-transform duration-200 group-hover:translate-x-1"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         INFORMATIONS UTILES
    ════════════════════════════════════════ --}}
    <section class="py-16 bg-white bg-motif fade-in home-band">
        <div class="container mx-auto px-4">
            <div class="text-center mb-8">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">{{ __('home.infos_kicker') }}</span>
                <h2 class="text-3xl md:text-4xl font-bold text-navy mt-2">{{ __('home.infos_title') }}</h2>
                <p class="text-gray-500 text-sm max-w-xl mx-auto mt-3">
                    {{ __('home.infos_intro') }}
                </p>
            </div>
            <x-books-slider />
        </div>
    </section>


    {{-- ═══════════════════════════════════════
         ACTUALITÉS
    ════════════════════════════════════════ --}}
    <section class="py-16 bg-slate-100 bg-motif fade-in home-band">
        <div class="container mx-auto px-4">
            <div class="text-center mb-10">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">{{ __('home.news_kicker') }}</span>
                <h2 class="text-3xl md:text-4xl font-bold text-navy mt-2">{{ __('home.news_title') }}</h2>
                <p class="text-gray-500 text-sm max-w-xl mx-auto mt-3">
                    {{ __('home.news_intro') }}
                </p>
                @if($latestActualites->isNotEmpty())
                    <a href="{{ route('actualites.index') }}"
                       class="inline-flex items-center justify-center mt-5 px-5 py-2.5 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold text-sm rounded-xl transition-all duration-300">
                        {{ __('home.news_more') }}
                    </a>
                @endif
            </div>
            @if($latestActualites->isEmpty())
                <div class="text-center text-gray-400 py-10">
                    <i class="fas fa-newspaper text-3xl mb-3 block"></i>
                    {{ __('home.news_empty') }}
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
         PUBLICATIONS DES RAPPORTS
    ════════════════════════════════════════ --}}
    <section class="py-16 bg-white bg-motif fade-in home-band">
        <div class="container mx-auto px-4">
            <div class="text-center mb-10">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">{{ __('home.reports_kicker') }}</span>
                <h2 class="text-3xl md:text-4xl font-bold text-navy mt-2">{{ __('home.reports_title') }}</h2>
                <p class="text-gray-500 text-sm max-w-xl mx-auto mt-3">
                    {{ __('home.reports_intro') }}
                </p>
                @if($recentReports->isNotEmpty())
                    <a href="{{ route('reports.index') }}"
                       class="inline-flex items-center justify-center mt-5 px-5 py-2.5 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold text-sm rounded-xl transition-all duration-300">
                        {{ __('home.reports_more') }}
                    </a>
                @endif
            </div>

            @if($recentReports->isEmpty())
                <div class="text-center text-gray-400 py-10">
                    <i class="fas fa-folder-open text-3xl mb-3 block"></i>
                    {{ __('home.reports_empty') }}
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
    <section class="py-14 bg-navy bg-motif-dark fade-in home-band">
        <div class="container mx-auto px-4">
            <div class="px-4 py-4 md:px-8 md:py-6 text-center">
                <span class="text-xs font-bold text-blue-200 uppercase tracking-widest">{{ __('home.newsletter_kicker') }}</span>
                <h2 class="text-3xl md:text-4xl font-bold text-white mt-2 mb-3">
                    {{ __('home.newsletter_title') }}
                </h2>
                <p class="text-blue-200 mb-7 max-w-lg mx-auto">
                    {{ __('home.newsletter_intro') }}
                </p>
                <form method="POST" action="{{ route('newsletter.souscription') }}"
                      class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    @csrf
                    <div class="flex-1">
                        <input type="email" name="email" required
                               placeholder="{{ __('home.newsletter_placeholder') }}"
                               class="w-full px-4 py-3 rounded-xl bg-white border border-gray-200 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 text-sm">
                    </div>
                    <button type="submit"
                            class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm rounded-lg transition-colors shrink-0">
                        {{ __('home.newsletter_submit') }}
                    </button>
                </form>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         NOS PARTENAIRES
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-white bg-motif fade-in home-band">
        <div class="container mx-auto px-4">
            <div class="text-center mb-8">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">{{ __('home.partners_kicker') }}</span>
                <h2 class="text-3xl md:text-4xl font-bold text-navy mt-2">{{ __('home.partners_title') }}</h2>
            </div>
            <x-institutions-carousel speed="40" />
        </div>
    </section>
@endsection
