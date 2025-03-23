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
                <div class="space-y-8 border-r-2 border-gray-100 pr-8">
                    <div class="space-y-4">
                        <h2 class="text-3xl font-bold text-blue-900 mb-6">Connectez-vous avec nous</h2>
                        <div class="flex items-start space-x-4 group">
                            <div class="p-3 rounded-lg transition-colors">
                                📍
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Notre siège social</h3>
                                <p class="text-gray-600">Port-au-Prince, Haïti</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4 group">
                            <div class="p-3 rounded-lg transition-colors">
                                📞
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Numéro de contact</h3>
                                <p class="text-gray-600">+509 1234-5678</p>
                                <p class="text-gray-600">Lun-Ven : 8h00 - 17h00</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4 group">
                            <div class="p-3 rounded-lg transition-colors">
                                ✉️
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Adresse e-mail</h3>
                                <p class="text-blue-600 hover:text-blue-800 transition-colors">
                                    <a href="mailto:contact@mef.gouv.ht">contact@mef.gouv.ht</a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 pt-8 border-t border-gray-100">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <h3 class="text-xl font-semibold text-gray-800">Rejoignez notre communauté</h3>
                            <div class="flex space-x-3">
                                <a href="#"
                                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-gray-100 text-gray-600 
                                          hover:bg-blue-900 hover:text-white transition-all duration-300
                                          transform hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-200/50"
                                    aria-label="Page Facebook">
                                    <i class="fab fa-facebook-f text-xl"></i>
                                </a>
                                <a href="#"
                                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-gray-100 text-gray-600 
                                          hover:bg-blue-400 hover:text-white transition-all duration-300
                                          transform hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-200/50"
                                    aria-label="Profil Twitter">
                                    <i class="fab fa-twitter text-xl"></i>
                                </a>
                                <a href="#"
                                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-gray-100 text-gray-600 
                                          hover:bg-blue-700 hover:text-white transition-all duration-300
                                          transform hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-200/50"
                                    aria-label="Profil LinkedIn">
                                    <i class="fab fa-linkedin-in text-xl"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de contact -->
                <div class="space-y-6">
                    <h2 class="text-3xl font-bold text-blue-900">Envoyer un message</h2>
                    <form class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="relative">
                                <input type="text" id="first-name" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent peer"
                                    placeholder=" ">
                                <label for="first-name"
                                    class="absolute left-4 text-gray-500 transition-all pointer-events-none
                                           peer-placeholder-shown:text-base peer-placeholder-shown:top-3
                                           peer-focus:-top-2 peer-focus:text-sm peer-focus:text-blue-600
                                           -top-2 text-sm bg-white px-1">
                                    Prénom
                                </label>
                            </div>
                            <div class="relative">
                                <input type="text" id="last-name" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent peer"
                                    placeholder=" ">
                                <label for="last-name"
                                    class="absolute left-4 text-gray-500 transition-all pointer-events-none
                                           peer-placeholder-shown:text-base peer-placeholder-shown:top-3
                                           peer-focus:-top-2 peer-focus:text-sm peer-focus:text-blue-600
                                           -top-2 text-sm bg-white px-1">
                                    Nom
                                </label>
                            </div>
                        </div>

                        <div class="relative">
                            <input type="email" id="email" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent peer"
                                placeholder=" ">
                            <label for="email"
                                class="absolute left-4 text-gray-500 transition-all pointer-events-none
                                       peer-placeholder-shown:text-base peer-placeholder-shown:top-3
                                       peer-focus:-top-2 peer-focus:text-sm peer-focus:text-blue-600
                                       -top-2 text-sm bg-white px-1">
                                Adresse e-mail
                            </label>
                        </div>

                        <div class="relative">
                            <textarea id="message" rows="4" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent peer"
                                placeholder=" "></textarea>
                            <label for="message"
                                class="absolute left-4 text-gray-500 transition-all pointer-events-none
                                       peer-placeholder-shown:text-base peer-placeholder-shown:top-3
                                       peer-focus:-top-2 peer-focus:text-sm peer-focus:text-blue-600
                                       -top-2 text-sm bg-white px-1">
                                Votre message
                            </label>
                        </div>

                        <button type="submit"
                            class="w-full py-3.5 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 
                                   transition-all transform hover:scale-[1.02] shadow-lg hover:shadow-blue-200">
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
