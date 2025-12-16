@extends('layouts.main')

@section('title', $actu->title)

@section('content')
<section class="flex justify-center bg-gray-50 fade-in">
    <div class="container mx-auto px-4 max-w-4xl">

        <!-- Card -->
        <article class="bg-white shadow-sm overflow-hidden">

            <!-- Header -->
            <div class="p-6 md:p-8 border-b border-gray-100">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">
                    {{ $actu->title }}
                </h1>

                <!-- Meta info -->
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    @if($actu->category)
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-medium">
                            {{ $actu->category }}
                        </span>
                    @endif

                    @if($actu->published_at)
                        <span class="flex items-center gap-1">
                            <i class="fa-regular fa-calendar"></i>
                            {{ $actu->published_at->translatedFormat('d F Y') }}
                        </span>
                    @endif

                    @if($actu->posted_in)
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-building-columns"></i>
                            {{ $actu->posted_in }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Carousel -->
            <div class="bg-gray-100">
                <x-carousel>
                    @if($actu->images->count())
                        @foreach($actu->images as $image)
                            <div class="swiper-slide">
                                <img src="{{ Storage::url($image->image_path) }}"
                                    class="w-full h-72 md:h-96 object-cover" alt="Image de l’actualité" />
                            </div>
                        @endforeach
                    @else
                        <div class="swiper-slide">
                            <img src="{{ asset('images/image_placeholder.png') }}"
                                class="w-full h-72 md:h-96 object-cover" alt="Image par défaut" />
                        </div>
                    @endif
                </x-carousel>
            </div>

            <!-- Content -->
            <div class="p-6 md:p-8 prose prose-gray max-w-none">
                @if($actu->content_text)
                    {!! nl2br(e($actu->content_text)) !!}
                @else
                    <p class="text-gray-700 leading-relaxed">
                        {{ $actu->description }}
                    </p>
                @endif
            </div>

            <!-- Footer -->
            <div class="px-6 md:px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                <a href="{{ route('actualites.index') }}"
                    class="inline-flex items-center gap-2 text-blue-600 font-medium hover:text-blue-800">
                    ← Retour aux actualités
                </a>

                <span class="text-xs text-gray-400">
                    Dernière mise à jour :
                    {{ $actu->updated_at->translatedFormat('d F Y') }}
                </span>
            </div>

        </article>

    </div>
    <div class="pt-5">
        <x-presentation role="Le Ministre" nom="Alfred Fils METELLUS" sexe="M"
            lien-profil="{{ route('quisommesnous.profil', ['role' => 'ministre']) }}"
            lien-discours="{{ route('quisommesnous.mots', ['role' => 'ministre']) }}"
            mobile-image="images/photo-metelus.png" desktop-image="images/photo-metelus.png" :showProfileLink="true"
            :showSpeechLink="true" />
    </div>
</section>
@endsection
