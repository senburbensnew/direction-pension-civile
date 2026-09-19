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
</style>
<div class="home-page">
    <section class="w-full overflow-hidden">

        <x-carousel>
            <div class="swiper-slide">
                <div class="hero-accueil w-full h-full grid grid-cols-1 md:grid-cols-2 items-center">
                    <div class="px-14 sm:px-16 lg:px-20 py-8">
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-navy/50">Direction de la Pension Civile</p>
                        <h1 class="mt-2 text-4xl md:text-5xl font-bold text-navy tracking-tight">
                            {{ __('home.hero_title') }}
                        </h1>
                        <p class="mt-3 text-xl md:text-2xl font-semibold text-orange-500">
                            {{ __('home.hero_tagline') }}
                        </p>
                        <p class="mt-4 text-navy/70 text-sm md:text-base max-w-md leading-relaxed">
                            {{ __('home.hero_body') }}
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('simulateur-calcul') }}"
                               class="inline-flex items-center justify-center px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm rounded-full shadow-lg shadow-orange-500/25 transition-colors">
                                {{ __('home.hero_cta_calcul') }}
                            </a>
                            <a href="#guide-pensionne"
                               class="inline-flex items-center justify-center px-5 py-2.5 bg-white/80 hover:bg-white text-navy font-semibold text-sm rounded-full border border-navy/10 transition-colors">
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
    <section class="bg-white bg-motif">
        <div class="relative z-10 -mt-6 md:-mt-8 px-4">
            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-3">
                @foreach([
                    ['href' => '#guide-pensionne', 'icon' => 'fa-book-open', 'title' => 'home.quick_guide_title', 'desc' => 'home.quick_guide_desc'],
                    ['href' => route('simulateur-calcul'), 'icon' => 'fa-calculator', 'title' => 'home.quick_sim_title', 'desc' => 'home.quick_sim_desc'],
                    ['href' => '#nos-services', 'icon' => 'fa-building-columns', 'title' => 'home.quick_services_title', 'desc' => 'home.quick_services_desc'],
                    ['href' => route('contact'), 'icon' => 'fa-envelope', 'title' => 'home.quick_contact_title', 'desc' => 'home.quick_contact_desc'],
                ] as $quick)
                    <a href="{{ $quick['href'] }}" class="home-card home-card-hover p-5 flex items-start gap-4">
                        <span class="w-11 h-11 rounded-xl bg-navy text-white flex items-center justify-center shrink-0">
                            <i class="fas {{ $quick['icon'] }}"></i>
                        </span>
                        <span>
                            <span class="block font-bold text-navy">{{ __($quick['title']) }}</span>
                            <span class="block text-sm text-gray-500 mt-0.5 leading-snug">{{ __($quick['desc']) }}</span>
                        </span>
                    </a>
                @endforeach
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
                            <p class="text-sm text-gray-600 leading-relaxed">
                                {{ __('home.vision_body') }}
                            </p>
                        </article>

                        <article class="home-card p-6 md:p-7 border-t-4 border-t-navy">
                            <h3 class="text-lg font-bold text-navy mb-3">{{ __('home.mission_title') }}</h3>
                            <ul class="space-y-2.5 text-sm text-gray-600">
                                @foreach(['home.mission_1', 'home.mission_2', 'home.mission_3', 'home.mission_4'] as $point)
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check text-orange-500 mt-0.5 text-xs"></i>
                                        <span>{{ __($point) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </article>

                        <article class="rounded-[1.25rem] p-6 md:p-7 flex flex-col bg-navy bg-motif-dark text-white shadow-[0_16px_36px_-18px_rgba(23,48,82,0.55)]">
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
                            $href = $service['code'] ? route('services.show', $service['code']) : null;

                            return [
                                'icon' => $service['icon'] ?? 'fa-building',
                                'audience' => __('home.services_audience'),
                                'title' => $service['nom'],
                                'desc' => $service['resume'] ?? '',
                                'href' => $href,
                            ];
                        })->all();
                    @endphp
                    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 items-stretch">
                        @foreach($serviceCards as $card)
                            <article class="flex flex-col home-card home-card-hover overflow-hidden">
                                <div class="bg-navy bg-motif-dark px-4 py-3 text-white">
                                    <p class="text-[10px] font-bold text-blue-200 uppercase tracking-widest mb-1.5">{{ $card['audience'] }}</p>
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-md bg-white/10 text-white flex items-center justify-center shrink-0 text-sm">
                                            <i class="fas {{ $card['icon'] }}"></i>
                                        </span>
                                        <h3 class="text-sm font-bold leading-snug">{{ $card['title'] }}</h3>
                                    </div>
                                    <p class="text-xs text-blue-100/90 mt-2 leading-relaxed line-clamp-2">{{ $card['desc'] }}</p>
                                </div>
                                @if($card['href'])
                                    <a href="{{ $card['href'] }}"
                                       class="group flex items-center gap-2 px-4 py-2 text-xs font-medium text-gray-600 hover:bg-slate-50 hover:text-navy transition-colors border-t border-slate-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-400 shrink-0"></span>
                                        <span class="flex-1">{{ __('home.services_see') }}</span>
                                        <i class="fas fa-arrow-right text-[10px] text-gray-300 group-hover:text-orange-500 group-hover:translate-x-0.5 transition-all"></i>
                                    </a>
                                @endif
                            </article>
                        @endforeach
                    </div>
        </div>
    </section>


    {{-- ═══════════════════════════════════════
         ACCÈS RAPIDE
    ════════════════════════════════════════ --}}
    <section class="py-16 md:py-20 bg-white bg-motif">
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
            <x-home-heading :kicker="__('home.acces_kicker')" :title="__('home.acces_title')" :intro="__('home.acces_intro')" />
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($accesRapide as $item)
                    <a href="{{ $item['href'] }}"
                       class="group home-card home-card-hover p-6 md:p-7 flex flex-col">
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
    <section class="py-16 md:py-20 bg-[#eef2f7] bg-motif">
        <div class="container mx-auto px-4">
            <x-home-heading :kicker="__('home.infos_kicker')" :title="__('home.infos_title')" :intro="__('home.infos_intro')" />
            <x-books-slider />
        </div>
    </section>


    {{-- ═══════════════════════════════════════
         ACTUALITÉS
    ════════════════════════════════════════ --}}
    <section class="py-16 md:py-20 bg-white bg-motif">
        <div class="container mx-auto px-4">
            <x-home-heading :kicker="__('home.news_kicker')" :title="__('home.news_title')" :intro="__('home.news_intro')" />
                @if($latestActualites->isNotEmpty())
                    <div class="text-center -mt-6 mb-8">
                    <a href="{{ route('actualites.index') }}"
                       class="inline-flex items-center justify-center px-5 py-2.5 bg-navy hover:bg-orange-500 text-white font-semibold text-sm rounded-full transition-colors">
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
                       class="inline-flex items-center justify-center px-5 py-2.5 bg-navy hover:bg-orange-500 text-white font-semibold text-sm rounded-full transition-colors">
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
    <section class="py-16 md:py-20 bg-white bg-motif">
        <div class="container mx-auto px-4">
            <x-home-heading :kicker="__('home.institution_images_kicker')" :title="__('home.institution_images_title')" :intro="__('home.institution_images_intro')" />
            <x-auto-slide-carousel />
        </div>
    </section>
    @endif

    {{-- ═══════════════════════════════════════
         NEWSLETTER
    ════════════════════════════════════════ --}}
    <section class="py-16 bg-navy bg-motif-dark">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto rounded-[1.75rem] border border-white/10 bg-white/5 px-6 py-10 md:px-12 md:py-12 text-center backdrop-blur-sm">
                <x-home-heading tone="light" :kicker="__('home.newsletter_kicker')" :title="__('home.newsletter_title')" :intro="__('home.newsletter_intro')" />
                <form method="POST" action="{{ route('newsletter.souscription') }}"
                      class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    @csrf
                    <div class="flex-1">
                        <input type="email" name="email" required
                               placeholder="{{ __('home.newsletter_placeholder') }}"
                               class="w-full px-4 py-3 rounded-full bg-white border border-gray-200 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-300 text-sm">
                    </div>
                    <button type="submit"
                            class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm rounded-full transition-colors shrink-0">
                        {{ __('home.newsletter_submit') }}
                    </button>
                </form>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════
         NOS PARTENAIRES
    ════════════════════════════════════════ --}}
    <section class="py-14 bg-white bg-motif">
        <div class="container mx-auto px-4">
            <x-home-heading :kicker="__('home.partners_kicker')" :title="__('home.partners_title')" />
            <x-institutions-carousel speed="40" />
        </div>
    </section>
</div>
@endsection
