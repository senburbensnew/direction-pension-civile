@extends('layouts.main')

@section('title', 'Accueil')

@section('content')
    <div class="bg-gray-50 text-gray-800 font-sans">
{{--     <x-carousel>
            <div class="swiper-slide">
                <img src="{{ asset('images/carousel/slide1.jpg') }}" alt="Slide 1">
            </div>
            <div class="swiper-slide">
                <img src="{{ asset('images/carousel/slide2.jpg') }}" alt="Slide 2">
            </div>
            <div class="swiper-slide">
                <img src="{{ asset('images/carousel/slide3.jpg') }}" alt="Slide 3">
            </div>
            <div class="swiper-slide">
                <img src="{{ asset('images/carousel/slide4.jpg') }}" alt="Slide 3">
            </div>
            <div class="swiper-slide">
                <img src="{{ asset('images/carousel/slide5.jpg') }}" alt="Slide 3">
            </div>
        </x-carousel> --}}
        <!-- About Section -->
        <section class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row items-center gap-8">
                <div class="relative w-full lg:w-1/2">
                    <img src="{{ asset('images/photo_2025-11-21_06-42-22.jpg') }}" alt="Pension Civile" class="rounded-lg shadow-lg">
                </div>
                <div class="w-full lg:w-1/2">
                    <h2 class="text-3xl font-bold mb-4">Votre guide vers la Pension Civile</h2>
                    <p class="mb-4 text-gray-600">
                        La Direction de la Pension Civile accompagne les fonctionnaires et retraités dans toutes leurs démarches,
                        de la constitution du dossier jusqu’au suivi des prestations.
                    </p>
                    <p class="mb-4 italic text-gray-500">"Assurer vos droits et votre avenir, notre priorité."</p>
                    <div class="flex items-center gap-2 text-gray-700 mb-4">
                        <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path d="M6 2a1 1 0 011-1h6a1 1 0 011 1v4H6V2zM5 7h10v9a1 1 0 01-1 1H6a1 1 0 01-1-1V7z"/></svg>
                        <span>15 Ans d’Expérience</span>
                    </div>
                    <button class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">En savoir plus</button>
                </div>
            </div>
        </section>

        <!-- Core Principles -->
        <section class="bg-blue-900 text-white mt-16 py-12">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold mb-6 text-center">Nos missions et valeurs</h2>
                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="bg-white text-gray-800 rounded p-6">
                        <h3 class="font-bold mb-2">Notre Vision</h3>
                        <p>Assurer la sécurité et la transparence dans le traitement des pensions civiles, pour un avenir serein des fonctionnaires et retraités.</p>
                    </div>
                    <div class="bg-white text-gray-800 rounded p-6">
                        <h3 class="font-bold mb-3 text-lg">Notre Mission</h3>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Traitement et liquidation des dossiers de pension</li>
                            <li>Élaboration et suivi des réformes en matière de pension civile</li>
                            <li>Gestion des paiements et contrôle des bénéficiaires</li>
                            <li>Conseil et assistance aux fonctionnaires et retraités</li>
                        </ul>
                    </div>
                    <div class="flex justify-center items-center">
                        <button class="bg-orange-500 px-6 py-4 rounded hover:bg-orange-600 text-white font-bold">Visionner Vidéo</button>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-2 md:grid-cols-4 text-center mt-12 gap-4">
                    <div>
                        <div class="text-2xl font-bold">680</div>
                        <div>Bénéficiaires Confiants</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">1,354</div>
                        <div>Dossiers Traitée</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">97%</div>
                        <div>Taux de Satisfaction</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">15Y</div>
                        <div>Années d’Expérience</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="container mx-auto mt-16 px-4">
            <h2 class="text-3xl font-bold mb-6">Rencontrez notre équipe d’experts</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <img src="{{ asset('images/carousel/slide2.jpg') }}" alt="Équipe Pension Civile" class="rounded shadow-lg">
                </div>
                <div class="flex flex-col justify-center">
                    <p class="text-gray-600 mb-4">
                        Notre équipe d’experts accompagne chaque bénéficiaire avec professionnalisme et dévouement,
                        garantissant des services fiables et transparents.
                    </p>
                    <button class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">Découvrir l’équipe</button>
                </div>
            </div>
        </section>

        <!-- Consultation CTA -->
        <section class="bg-blue-900 text-white py-16 mt-16">
            <div class="container mx-auto text-center px-4">
                <h2 class="text-3xl font-bold mb-4">Informez-vous sur vos droits à la Pension Civile</h2>
                <p class="mb-6 text-gray-200">
                    La Direction de la Pension Civile vous accompagne dans la compréhension de vos droits,
                    vos démarches administratives, ainsi que le calcul et le suivi de vos prestations.
                    Découvrez les informations essentielles pour préparer votre avenir en toute sérénité.
                </p>
                <button class="bg-orange-500 text-white px-6 py-3 rounded hover:bg-orange-600">
                    En savoir plus
                </button>
            </div>
        </section>
        <x-mediatheque />
    </div>
@endsection
