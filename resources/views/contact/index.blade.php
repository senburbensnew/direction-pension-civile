@extends('layouts.main')

@section('title', 'Contact')

@section('content')
    <!-- Section Héro -->
    <div class="w-full bg-blue-900 text-white py-3 md:py-4 text-center font-bold text-xl md:text-2xl"
        style="background-color: #074482;">
        Contactez-nous
    </div>

    <!-- Section Contact -->
    <section class="relative pt-16 rounded-2xl border-1">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 bg-white rounded-2xl p-8 md:p-12 transform -translate-y-12">
                <!-- Informations de contact -->
                <div class="grid grid-cols-1 gap-12 border-r-2 border-gray-100 pr-8">
                    <!-- Contact Information Section -->
                    <div class="grid grid-cols-1 gap-8">
                        <h2 class="text-3xl font-bold text-blue-900">Connectez-vous avec nous</h2>

                        <!-- Contact Items Grid -->
                        <div class="grid grid-cols-[auto_1fr] items-start gap-4">
                            <!-- Address -->
                            <div class="p-3 rounded-lg transition-colors row-span-1">📍</div>
                            <div class="grid gap-1">
                                <h3 class="text-lg font-semibold text-gray-800">Notre siège social</h3>
                                <p class="text-gray-600">5, Avenue Charles Sumner Port-au-Prince, Haïti (W.I)</p>
                            </div>

                            <!-- Phone -->
                            <div class="p-3 rounded-lg transition-colors row-span-1">📞</div>
                            <div class="grid gap-1">
                                <h3 class="text-lg font-semibold text-gray-800">Numéro de contact</h3>
                                <p class="text-gray-600">+(509) 29 92 1007</p>
                                <p class="text-gray-600">Lun-Ven : 8h00 - 16h00</p>
                            </div>

                            <!-- Email -->
                            <div class="p-3 rounded-lg transition-colors row-span-1">✉️</div>
                            <div class="grid gap-1">
                                <h3 class="text-lg font-semibold text-gray-800">Adresse e-mail</h3>
                                <p class="text-blue-600 hover:text-blue-800 transition-colors">
                                    <a href="mailto:contact@mef.gouv.ht">info@mef.gouv.ht</a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Section -->
                    <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-6 pt-8 border-t border-gray-100">
                        <h3 class="text-xl font-semibold text-gray-800 self-center">Rejoignez notre communauté</h3>

                        <div class="flex justify-end gap-3">
                            <!-- Social Icons -->
                            <a href="#"
                                class="w-12 h-12 grid place-items-center rounded-xl bg-gray-100 text-gray-600 
                                hover:bg-blue-900 hover:text-white transition-all duration-300
                                hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-200/50"
                                aria-label="Page Facebook">
                                <i class="fab fa-facebook-f text-xl"></i>
                            </a>
                            <a href="#"
                                class="w-12 h-12 grid place-items-center rounded-xl bg-gray-100 text-gray-600 
                                hover:bg-blue-400 hover:text-white transition-all duration-300
                                hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-200/50"
                                aria-label="Profil Twitter">
                                <i class="fab fa-twitter text-xl"></i>
                            </a>
                            <a href="#"
                                class="w-12 h-12 grid place-items-center rounded-xl bg-gray-100 text-gray-600 
                                hover:bg-blue-700 hover:text-white transition-all duration-300
                                hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-200/50"
                                aria-label="Profil LinkedIn">
                                <i class="fab fa-linkedin-in text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de contact -->
                <div class="space-y-6">
                    <h2 class="text-3xl font-bold text-blue-900">Envoyer un message</h2>
                    <form class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="first-name" class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                                <input type="text" id="first-name" name="first-name" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="last-name" class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                                <input type="text" id="last-name" name="last-name" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Adresse
                                e-mail</label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Votre message</label>
                            <textarea id="message" name="message" rows="4" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>

                        <button type="submit"
                            class="w-full py-3 bg-blue-800 text-white font-medium rounded-md hover:bg-blue-900 transition-colors">
                            Envoyer le message →
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Carte -->
    <div class="max-w-7xl mx-auto px-6 mb-2">
        <div class="overflow-hidden border-2 border-white">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15130.65651919688!2d-72.3400433!3d18.544074!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x410db1f320d7ae01!2sMinist%C3%A8re%20de%20l'%C3%89conomie%20et%20des%20Finances%20(MEF)!5e0!3m2!1sen!2sht!4v1592604157945!5m2!1sen!2sht"
                width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
@endsection
