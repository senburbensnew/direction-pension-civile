@extends('layouts.main')

@section('title', 'Contact')

@section('content')
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card-shadow {
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1), 0 10px 20px -5px rgba(0, 0, 0, 0.04);
        }

        .contact-card {
            transition: all 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
        }

        .icon-container {
            transition: all 0.3s ease;
        }

        .contact-item:hover .icon-container {
            transform: scale(1.1);
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }

        .contact-item:hover .icon-container i {
            color: white;
        }

        .social-icon {
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            transform: translateY(-3px) scale(1.1);
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #d1d5db, transparent);
            margin: 2rem 0;
        }

        .map-container {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
        }
    </style>

    <!-- Header Section -->
{{--     <section class="gradient-bg text-white py-16 fade-in">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Contactez-nous</h1>
            <p class="text-xl text-blue-100 max-w-2xl mx-auto">
                Nous sommes là pour répondre à vos questions et vous accompagner dans vos démarches
            </p>
        </div>
    </section> --}}

    <!-- Contact Form & Info Section -->
    <section class="relative fade-in">
        <div class="max-w-7xl mx-auto px-6">
            <div class="bg-white rounded-2xl card-shadow overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                    <!-- Informations de contact -->
                    <div class="p-10 bg-gradient-to-br from-gray-50 to-blue-50">
                        <div class="mb-10">
                            <h2 class="text-3xl font-bold gradient-text mb-2">Connectez-vous avec nous</h2>
                            <p class="text-gray-600">Notre équipe est à votre disposition pour vous accompagner</p>
                        </div>

                        <!-- Contact Items -->
                        <div class="space-y-8">
                            <!-- Address -->
                            <div class="contact-item flex items-start gap-4">
                                <div class="icon-container w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-1">Notre siège social</h3>
                                    <p class="text-gray-600">5, Avenue Charles Sumner Port-au-Prince, Haïti (W.I)</p>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="contact-item flex items-start gap-4">
                                <div class="icon-container w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-phone-alt text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-1">Numéro de contact</h3>
                                    <p class="text-gray-600">+(509) 29 92 1007</p>
                                    <p class="text-gray-600 text-sm mt-1">Lun-Ven : 8h00 - 16h00</p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="contact-item flex items-start gap-4">
                                <div class="icon-container w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-envelope text-purple-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-1">Adresse e-mail</h3>
                                    <a href="mailto:contact@mef.gouv.ht" class="text-blue-600 hover:text-blue-800 transition-colors text-lg font-medium">
                                        info@mef.gouv.ht
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media Section -->
                        <div class="section-divider"></div>

                        <div class="pt-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6">Rejoignez notre communauté</h3>

                            <div class="flex gap-4">
                                <!-- Facebook -->
                                <a href="#" class="social-icon w-12 h-12 flex items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700" aria-label="Page Facebook">
                                    <i class="fab fa-facebook-f text-lg"></i>
                                </a>

                                <!-- Twitter -->
                                <a href="#" class="social-icon w-12 h-12 flex items-center justify-center rounded-xl bg-blue-400 text-white hover:bg-blue-500" aria-label="Profil Twitter">
                                    <i class="fab fa-twitter text-lg"></i>
                                </a>

                                <!-- LinkedIn -->
                                <a href="#" class="social-icon w-12 h-12 flex items-center justify-center rounded-xl bg-blue-700 text-white hover:bg-blue-800" aria-label="Profil LinkedIn">
                                    <i class="fab fa-linkedin-in text-lg"></i>
                                </a>

                                <!-- YouTube -->
                                <a href="#" class="social-icon w-12 h-12 flex items-center justify-center rounded-xl bg-red-600 text-white hover:bg-red-700" aria-label="Chaîne YouTube">
                                    <i class="fab fa-youtube text-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire de contact -->
                    <div class="p-10">
                        <div class="mb-8">
                            <h2 class="text-3xl font-bold text-gray-800 mb-2">Envoyer un message</h2>
                            <p class="text-gray-600">Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais</p>
                        </div>

                        <form class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label for="first-name" class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                                    <div class="relative">
                                        <input type="text" id="first-name" name="first-name" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none transition-colors">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="last-name" class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                                    <div class="relative">
                                        <input type="text" id="last-name" name="last-name" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none transition-colors">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Adresse e-mail</label>
                                <div class="relative">
                                    <input type="email" id="email" name="email" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none transition-colors">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Sujet</label>
                                <div class="relative">
                                    <select id="subject" name="subject" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none transition-colors appearance-none">
                                        <option value="" disabled selected>Sélectionnez un sujet</option>
                                        <option value="pension">Question sur les pensions</option>
                                        <option value="documents">Demande de documents</option>
                                        <option value="rendezvous">Prise de rendez-vous</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Votre message</label>
                                <div class="relative">
                                    <textarea id="message" name="message" rows="5" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:outline-none transition-colors resize-none"></textarea>
                                    <div class="absolute top-3 right-3 pointer-events-none">
                                        <i class="fas fa-pen text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-800 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-900 transition-all duration-300 flex items-center justify-center gap-2">
                                <span>Envoyer le message</span>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Carte -->
    <section class="max-w-7xl mx-auto px-6 pt-16 fade-in">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold gradient-text mb-3">Notre localisation</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Retrouvez-nous facilement à notre siège social situé au cœur de Port-au-Prince</p>
        </div>

        <div class="map-container">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15130.65651919688!2d-72.3400433!3d18.544074!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x410db1f320d7ae01!2sMinist%C3%A8re%20de%20l'%C3%89conomie%20et%20des%20Finances%20(MEF)!5e0!3m2!1sen!2sht!4v1592604157945!5m2!1sen!2sht"
                width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
            <div class="bg-white p-6 rounded-xl border border-gray-200 text-center contact-card">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clock text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Heures d'ouverture</h3>
                <p class="text-gray-600">Lundi - Vendredi</p>
                <p class="text-gray-600 font-medium">8h00 - 16h00</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 text-center contact-card">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-phone-alt text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Support téléphonique</h3>
                <p class="text-gray-600">+(509) 29 92 1007</p>
                <p class="text-gray-600">Lun-Ven: 8h-16h</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 text-center contact-card">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-envelope-open-text text-purple-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Support par email</h3>
                <p class="text-gray-600">info@mef.gouv.ht</p>
                <p class="text-gray-600">Réponse sous 48h</p>
            </div>
        </div>
    </section>
@endsection
