@extends('layouts.main')

@section('title', __('messages.home'))

@section('content')
<style>
    .home-page .hero-carousel {
        height: 340px;
    }
    @media (min-width: 768px)  { .home-page .hero-carousel { height: 440px; } }
    @media (min-width: 1024px) { .home-page .hero-carousel { height: 560px; } }

    .hero-accueil {
        background:
            radial-gradient(1200px 420px at 15% 10%, rgba(255,255,255,.45), transparent 55%),
            linear-gradient(135deg, #f4f8fc 0%, #d5e4f2 42%, #173052 100%);
    }

    .hero-carousel .swiper-slide img.hero-accueil-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 38%;
        -webkit-mask-image: linear-gradient(to right, transparent 0%, #000 22%, #000 100%);
        mask-image: linear-gradient(to right, transparent 0%, #000 22%, #000 100%);
    }

    .home-card {
        background: #fff;
        border: 1px solid rgba(23, 48, 82, 0.08);
        box-shadow: 0 10px 30px -16px rgba(23, 48, 82, 0.28);
        border-radius: 1.25rem;
    }

    .home-card-hover {
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }
    .home-card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 22px 40px -18px rgba(23, 48, 82, 0.35);
        border-color: rgba(249, 115, 22, 0.35);
    }

    #guide-pensionne .guide-pensionne-content .home-card,
    #guide-pensionne .guide-pensionne-content article {
        border-radius: 0;
    }

    .home-tabs {
        scrollbar-width: none;
    }
    .home-tabs::-webkit-scrollbar {
        display: none;
    }

    .home-page .hero-dots {
        bottom: 5.5rem;
    }
    @media (min-width: 768px) {
        .home-page .hero-dots {
            bottom: 6.5rem;
        }
    }

    .home-service-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        align-items: stretch;
    }
    @media (min-width: 640px) {
        .home-service-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (min-width: 1280px) {
        .home-service-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 2rem; }
    }

    .home-service-tile {
        background: #0a1d4d;
        color: #fff;
        border-radius: 0.75rem;
        min-height: 168px;
        padding: 1.25rem 1.35rem 1.4rem;
        display: flex;
        flex-direction: column;
        text-decoration: none;
        box-shadow: none;
        transition: transform .22s ease, background-color .22s ease;
    }
    .home-service-tile:hover {
        transform: translateY(-3px);
        background: #0d2663;
        color: #fff;
    }
    .home-service-tile__title {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        line-height: 1.35;
        text-align: left;
        margin: 0;
    }
    .home-service-tile__icon {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.15rem;
        line-height: 1;
        font-weight: 300;
    }
</style>
<div class="home-page">
    <section class="w-full overflow-hidden">

        <x-carousel>
            <div class="swiper-slide">
                <div class="hero-accueil w-full h-full grid grid-cols-1 md:grid-cols-2 items-center">
                    <div class="px-14 sm:px-16 lg:px-20 py-8">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-navy/70">Direction de la Pension Civile</p>
                        <h1 class="mt-2 text-4xl md:text-5xl font-bold text-navy tracking-tight">
                            {{ __('home.hero_title') }}
                        </h1>
                        <p class="mt-3 text-xl md:text-2xl font-semibold text-orange-500">
                            {{ __('home.hero_tagline') }}
                        </p>
                        <p class="mt-4 text-navy/80 text-base md:text-lg max-w-md leading-relaxed">
                            {{ __('home.hero_body') }}
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('simulateur-calcul') }}"
                               class="inline-flex items-center justify-center px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-base rounded-full shadow-lg shadow-orange-500/25 transition-colors">
                                {{ __('home.hero_cta_calcul') }}
                            </a>
                            <a href="#guide-pensionne"
                               class="inline-flex items-center justify-center px-6 py-3 bg-white/80 hover:bg-white text-navy font-semibold text-base rounded-full border border-navy/10 transition-colors">
                                {{ __('home.quick_guide_title') }}
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
                    $hasOverlay = $slide->title || $slide->description || $slide->cta_label || $slide->link;
                    $ctaLabel = $slide->cta_label ?: __('home.carousel_cta');
                    $isExternalLink = $slide->link && preg_match('#^https?://#i', $slide->link);
                @endphp
                <div class="swiper-slide{{ $slide->link ? ' cursor-pointer' : '' }}">
                    <img src="{{ $slide->imageUrl() }}"
                         alt="{{ $slide->title ?? 'Direction de la Pension Civile' }}"
                         loading="lazy">

                    @if($hasOverlay)
                        {{-- Gradient overlay --}}
                        <div class="absolute inset-0 {{ $gradientClass }} pointer-events-none"></div>

                        {{-- Text overlay --}}
                        <div class="absolute inset-0 flex p-6 sm:p-10 {{ $alignClass }} pointer-events-none">
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
                                @if($slide->link || $slide->cta_label)
                                    <span class="inline-flex items-center gap-2 bg-white text-gray-900 font-semibold text-sm px-5 py-2.5 rounded-full shadow-lg">
                                        {{ $slide->link ? $ctaLabel : $slide->cta_label }}
                                        @if($slide->link)
                                            <i class="fas fa-arrow-right text-xs"></i>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($slide->link)
                        <a href="{{ $slide->link }}"
                           class="absolute inset-0 z-[5]"
                           aria-label="{{ $ctaLabel }}"
                           @if($isExternalLink) target="_blank" rel="noopener noreferrer" @endif></a>
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
    <section class="bg-white">
        <div class="relative z-10 -mt-6 md:-mt-8">
            <div class="container mx-auto px-4">
                <p class="sr-only">{{ __('home.essentials_title') }}</p>
                <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach([
                        ['href' => route('simulateur-calcul'), 'icon' => 'fa-calculator', 'title' => 'home.quick_sim_title', 'desc' => 'home.quick_sim_desc'],
                        ['href' => '#guide-pensionne', 'icon' => 'fa-book-open', 'title' => 'home.quick_guide_title', 'desc' => 'home.quick_guide_desc'],
                        ['href' => route('contact'), 'icon' => 'fa-envelope', 'title' => 'home.quick_contact_title', 'desc' => 'home.quick_contact_desc'],
                        ['href' => '#nos-services', 'icon' => 'fa-building-columns', 'title' => 'home.quick_services_title', 'desc' => 'home.quick_services_desc'],
                    ] as $quick)
                        <a href="{{ $quick['href'] }}" class="home-card home-card-hover p-5 md:p-6 flex items-start gap-4">
                            <span class="w-12 h-12 rounded-xl bg-navy text-white flex items-center justify-center shrink-0 text-lg">
                                <i class="fas {{ $quick['icon'] }}"></i>
                            </span>
                            <span>
                                <span class="block font-bold text-navy text-lg leading-snug">{{ __($quick['title']) }}</span>
                                <span class="block text-base text-gray-600 mt-1 leading-relaxed">{{ __($quick['desc']) }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="container mx-auto px-4 pt-10 pb-16 md:pt-12 md:pb-20">
            <div class="grid lg:grid-cols-12 gap-8 items-start">
                <aside class="lg:col-span-3 max-w-sm mx-auto lg:max-w-none lg:mx-0">
                    <div class="home-card overflow-hidden">
                        <x-presentation slug="ministre" variant="card" />
                    </div>
                </aside>
                <div class="lg:col-span-9">
                    <x-home-heading :kicker="__('home.comm_kicker')" :title="__('home.vision_mission_title')" />
                    <div class="grid md:grid-cols-3 gap-5 items-stretch">
                        <article class="home-card p-6 md:p-7 border-t-4 border-t-orange-500">
                            <h3 class="text-lg font-bold text-navy mb-2">{{ __('home.vision_title') }}</h3>
                            <p class="text-base text-gray-700 leading-relaxed">
                                {{ __('home.vision_body') }}
                            </p>
                        </article>

                        <article class="home-card p-6 md:p-7 border-t-4 border-t-navy">
                            <h3 class="text-lg font-bold text-navy mb-3">{{ __('home.mission_title') }}</h3>
                            <ul class="space-y-2.5 text-base text-gray-700 leading-relaxed">
                                @foreach(['home.mission_1', 'home.mission_2', 'home.mission_3', 'home.mission_4'] as $point)
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check text-orange-500 mt-0.5 text-xs"></i>
                                        <span>{{ __($point) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </article>

                        <article class="rounded-[1.25rem] p-6 md:p-7 flex flex-col bg-navy text-white shadow-[0_16px_36px_-18px_rgba(23,48,82,0.55)]">
                            <h3 class="text-lg font-bold text-white mb-1">{{ __('home.video_title') }}</h3>
                            <p class="text-base text-blue-50 mb-4 leading-relaxed">{{ __('home.video_subtitle') }}</p>
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
         INFORMATION PENSIONNÉS
    ════════════════════════════════════════ --}}
    @include('partials.home-pension-info')

    {{-- ═══════════════════════════════════════
         NOS SERVICES
    ════════════════════════════════════════ --}}
    <section id="nos-services" class="py-16 md:py-20 bg-[#eef2f7] bg-motif scroll-mt-24">
        <div class="container mx-auto px-4">
                    <x-home-heading :kicker="__('home.services_kicker')" :title="__('home.services_title')" :intro="__('home.services_intro')" />
                    @php
                        $serviceCards = collect(config('dpc_services'))->map(function ($service) {
                            return [
                                'icon' => $service['icon'] ?? 'fa-building',
                                'title' => $service['nom'],
                                'href' => $service['code'] ? route('services.show', $service['code']) : null,
                            ];
                        })->all();
                    @endphp
                    <div class="home-service-grid">
                        @foreach($serviceCards as $card)
                            @if($card['href'])
                                <a href="{{ $card['href'] }}" class="home-service-tile" aria-label="{{ $card['title'] }}">
                                    <h3 class="home-service-tile__title">{{ $card['title'] }}</h3>
                                    <span class="home-service-tile__icon" aria-hidden="true">
                                        <i class="fas {{ $card['icon'] }}"></i>
                                    </span>
                                </a>
                            @else
                                <article class="home-service-tile">
                                    <h3 class="home-service-tile__title">{{ $card['title'] }}</h3>
                                    <span class="home-service-tile__icon" aria-hidden="true">
                                        <i class="fas {{ $card['icon'] }}"></i>
                                    </span>
                                </article>
                            @endif
                        @endforeach
                    </div>
        </div>
    </section>


    {{-- ═══════════════════════════════════════
         ACCÈS RAPIDE
    ════════════════════════════════════════ --}}
    <section class="py-16 md:py-20 bg-white">
        <div class="container mx-auto px-4">
            @php
                $accesRapide = [
                    ['icon' => 'fa-calculator', 'title' => __('home.acces_simulateur_title'), 'desc' => __('home.acces_simulateur_desc'), 'href' => route('simulateur-calcul')],
                    ['icon' => 'fa-scale-balanced', 'title' => __('home.acces_textes_title'), 'desc' => __('home.acces_textes_desc'), 'href' => route('textes_documents_legaux')],
                    ['icon' => 'fa-circle-question', 'title' => __('home.acces_faq_title'), 'desc' => __('home.acces_faq_desc'), 'href' => route('faq.index')],
                    ['icon' => 'fa-photo-film', 'title' => __('home.acces_media_title'), 'desc' => __('home.acces_media_desc'), 'href' => route('mediatheque')],
                    ['icon' => 'fa-book', 'title' => __('home.acces_glossaire_title'), 'desc' => __('home.acces_glossaire_desc'), 'href' => route('glossaire')],
                    ['icon' => 'fa-envelope', 'title' => __('home.acces_contact_title'), 'desc' => __('home.acces_contact_desc'), 'href' => route('contact')],
                ];
            @endphp
            <x-home-heading :kicker="__('home.acces_kicker')" :title="__('home.acces_title')" :intro="__('home.acces_intro')" />
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($accesRapide as $item)
                    <a href="{{ $item['href'] }}"
                       class="group home-card home-card-hover px-5 py-4 flex items-center gap-4">
                        <span class="w-11 h-11 rounded-xl bg-navy text-white flex items-center justify-center shrink-0 group-hover:bg-orange-500 transition-colors duration-200">
                            <i class="fas {{ $item['icon'] }}"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-base font-bold text-navy">{{ $item['title'] }}</span>
                            <span class="block text-base text-gray-600 leading-relaxed mt-0.5">{{ $item['desc'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         INFORMATIONS UTILES
    ════════════════════════════════════════ --}}
    <section class="py-16 md:py-20 bg-[#eef2f7] bg-motif">
        <div class="container mx-auto px-4">
            <x-home-heading :kicker="__('home.infos_kicker')" :title="__('home.infos_title')" :intro="__('home.infos_intro')" />
            <x-books-slider />
        </div>
    </section>


    {{-- ═══════════════════════════════════════
         ACTUALITÉS
    ════════════════════════════════════════ --}}
    <section class="py-16 md:py-20 bg-white">
        <div class="container mx-auto px-4">
            <x-home-heading :kicker="__('home.news_kicker')" :title="__('home.news_title')" :intro="__('home.news_intro')" />
                @if($latestActualites->isNotEmpty())
                    <div class="text-center -mt-6 mb-8">
                    <a href="{{ route('actualites.index') }}"
                       class="inline-flex items-center justify-center px-6 py-3 bg-navy hover:bg-orange-500 text-white font-semibold text-base rounded-full transition-colors">
                        {{ __('home.news_more') }}
                    </a>
                    </div>
                @endif
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
    <section class="py-16 md:py-20 bg-[#eef2f7] bg-motif">
        <div class="container mx-auto px-4">
            <x-home-heading :kicker="__('home.reports_kicker')" :title="__('home.reports_title')" :intro="__('home.reports_intro')" />
                @if($recentReports->isNotEmpty())
                    <div class="text-center -mt-6 mb-8">
                    <a href="{{ route('reports.index') }}"
                       class="inline-flex items-center justify-center px-6 py-3 bg-navy hover:bg-orange-500 text-white font-semibold text-base rounded-full transition-colors">
                        {{ __('home.reports_more') }}
                    </a>
                    </div>
                @endif

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
         NOTRE INSTITUTION EN IMAGES
    ════════════════════════════════════════ --}}
    @if(\App\Models\InstitutionImage::active()->exists())
    <section class="py-16 md:py-20 bg-white">
        <div class="container mx-auto px-4">
            <x-home-heading :kicker="__('home.institution_images_kicker')" :title="__('home.institution_images_title')" :intro="__('home.institution_images_intro')" />
            <x-auto-slide-carousel />
        </div>
    </section>
    @endif

    {{-- ═══════════════════════════════════════
         NEWSLETTER
    ════════════════════════════════════════ --}}
    <section class="py-16 bg-navy">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto rounded-[1.75rem] border border-white/10 bg-white/5 px-6 py-10 md:px-12 md:py-12 text-center backdrop-blur-sm">
                <x-home-heading tone="light" :kicker="__('home.newsletter_kicker')" :title="__('home.newsletter_title')" :intro="__('home.newsletter_intro')" />
                <form method="POST" action="{{ route('newsletter.souscription') }}"
                      class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    @csrf
                    <div class="flex-1">
                        <input type="email" name="email" required
                               placeholder="{{ __('home.newsletter_placeholder') }}"
                               class="w-full px-4 py-3 rounded-full bg-white border border-gray-200 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-300 text-base">
                    </div>
                    <button type="submit"
                            class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-base rounded-full transition-colors shrink-0">
                        {{ __('home.newsletter_submit') }}
                    </button>
                </form>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         NOS PARTENAIRES
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-white">
        <div class="container mx-auto px-4">
            <x-home-heading :kicker="__('home.partners_kicker')" :title="__('home.partners_title')" />
            <x-institutions-carousel speed="40" />
        </div>
    </section>
</div>
@endsection
