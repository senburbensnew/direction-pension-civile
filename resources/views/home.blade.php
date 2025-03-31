@extends('layouts.main')

@section('title', 'Accueil')

@section('content')
    <x-carousel>
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
    </x-carousel>
    <div class="flex flex-col md:flex-row p-2 pt-4">
        <div class="md:w-4/5 w-full p-2">
            <div class="flex flex-col md:flex-row items-stretch">
                <x-presentation />
                <div class="px-4 mt-4 md:mt-0">
                    <x-article />
                    <x-section>
                        <x-slot name="title">
                            Section title
                        </x-slot>
                        <x-article />
                        <x-article />
                        <x-article />
                    </x-section>
                    <x-section>
                        <x-slot name="title">
                            Section title
                        </x-slot>
                        <x-article />
                        <x-article />
                    </x-section>
                </div>
            </div>
            <x-section>
                <x-slot name="title">
                    Section title
                </x-slot>
                <x-article />
                <x-article />
                <x-article />
                <x-article />
                <x-article />
                <x-article />
                <x-article />
                <x-article />
                <x-article />
            </x-section>
        </div>
        <x-nouveautes />
    </div>
    <div class="relative bg-gray-100 text-center py-10">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('assets/image.png');">
        </div>

        <!-- Content -->
        <div class="relative z-10 max-w-3xl mx-auto">
            <!-- Logo and Title -->
            <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Haiti Coat of Arms"
                class="mx-auto w-24 mb-4">
            <h1 class="text-2xl font-bold text-gray-800">DIRECTION DE LA PENSION CIVILE</h1>
            <h2 class="text-lg text-gray-700">RÉPUBLIQUE D'HAÏTI</h2>

            <!-- Subtitle -->
            <p class="mt-4 text-lg font-semibold text-blue-600">
                Officia animi adipisci sint architecto sit. <br> Adipisci placeat ab hic neque iure!
            </p>

            <!-- Button -->
            <button class="mt-6 px-6 py-2 bg-red-500 text-white rounded-lg shadow-md hover:bg-red-600 transition">
                LIRE PLUS »
            </button>
        </div>
    </div>
    <div class="relative bg-white text-center py-10" style="border-top: 3px solid #173152;">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('assets/image.png');">
        </div>
        <!-- Content -->
        <div class="relative z-10 max-w-5xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Statistics Section -->
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-lg font-bold text-gray-800 border-b pb-2">STATISTIQUES</h2>
                <p class="mt-2 text-sm text-gray-600">Évolution de l'indice des prix à la consommation (IPC) (Variation %)
                </p>
                <div class="flex justify-center my-3">
                    <img src="{{ 'images/chart.PNG' }}" alt="chart" class="w-full rounded-lg">
                </div>
            </div>

            <!-- Documentation Section -->
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-lg font-bold text-gray-800 border-b pb-2">DOCUMENTATION</h2>
                <ul class="mt-2 space-y-2">
                    <li
                        class="text-white flex items-center justify-between bg-blue-900 hover:bg-blue-950 px-4 py-3 rounded-lg shadow-md transition duration-200 hover:shadow-lg cursor-pointer">
                        FINANCES PUBLIQUES <span class="text-white text-lg">→</span>
                    </li>
                    <li
                        class="text-white flex items-center justify-between bg-blue-900 hover:bg-blue-950 px-4 py-3 rounded-lg shadow-md transition duration-200 hover:shadow-lg cursor-pointer">
                        MARCHÉS PUBLICS <span class="text-white text-lg">→</span>
                    </li>
                    <li
                        class="text-white flex items-center justify-between bg-blue-900 hover:bg-blue-950 px-4 py-3 rounded-lg shadow-md transition duration-200 hover:shadow-lg cursor-pointer">
                        COMMERCE EXTÉRIEUR <span class="text-white text-lg">→</span>
                    </li>
                    <li
                        class="text-white flex items-center justify-between bg-blue-900 hover:bg-blue-950 px-4 py-3 rounded-lg shadow-md transition duration-200 hover:shadow-lg cursor-pointer">
                        DÉPENSES COVID-19 <span class="text-white text-lg">→</span>
                    </li>
                    <li
                        class="text-white flex items-center justify-between bg-blue-900 hover:bg-blue-950 px-4 py-3 rounded-lg shadow-md transition duration-200 hover:shadow-lg cursor-pointer">
                        Comprendre le budget <span class="text-white text-lg">→</span>
                    </li>
                </ul>
            </div>

            <!-- News Section -->
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-lg font-bold text-gray-800 border-b pb-2">ACTUALITÉS DE LA DIRECTION</h2>
                <img src="https://picsum.photos/1000/500" alt="News" class="w-full mt-4">
                <p class="mt-2 text-sm text-gray-600 font-bold">La direction du FMI approuve un nouveau programme de
                    référence en faveur d’Haïti</p>
            </div>
        </div>
    </div>
    {{-- <x-mediatheque /> --}}
@endsection
