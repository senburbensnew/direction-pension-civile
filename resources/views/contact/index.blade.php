@extends('layouts.main')

@section('title', 'Contact')

@section('content')
    <!-- Main Template -->
    <div class="w-full bg-blue-900 text-white py-3 md:py-4 text-center font-bold text-xl md:text-2xl"
        style="background-color: #074482;">
        Contact Us
    </div>
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="space-y-6">
                    <h2 class="text-2xl font-bold text-blue-900">Contact Information</h2>
                    <p class="text-lg text-gray-600">
                        If you have any questions or need assistance, feel free to reach out to us.
                    </p>

                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-900" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.77 3.14a4.992 4.992 0 013.57-1.44c1.26 0 2.49.49 3.44 1.38l1.85 1.79 1.85-1.79c.95-.89 2.18-1.38 3.44-1.38 1.29 0 2.5.49 3.44 1.38l1.85 1.79 1.85-1.79c.95-.89 2.18-1.38 3.44-1.38 1.4 0 2.63.56 3.57 1.44.95.88 1.58 2.03 1.58 3.24s-.63 2.36-1.58 3.24l-1.85 1.79 1.85 1.79c.95.88 1.58 2.03 1.58 3.24s-.63 2.36-1.58 3.24c-1.26 0-2.49-.49-3.44-1.38l-1.85-1.79-1.85 1.79c-.95.89-2.18 1.38-3.44 1.38-1.26 0-2.49-.49-3.44-1.38l-1.85-1.79-1.85 1.79c-.95.89-2.18 1.38-3.44 1.38-1.29 0-2.5-.49-3.44-1.38l-1.85-1.79-1.85 1.79c-.95.88-2.18 1.38-3.44 1.38-1.4 0-2.63-.56-3.57-1.44-.95-.88-1.58-2.03-1.58-3.24s.63-2.36 1.58-3.24l1.85-1.79-1.85-1.79c-.95-.88-1.58-2.03-1.58-3.24s.63-2.36 1.58-3.24z" />
                            </svg>
                            <p class="text-lg">Phone: +509 1234-5678</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-900" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.25 12l8.25 5 8.25-5m-8.25-5l8.25 5-8.25-5zm-8.25 5v10.5l8.25-5.25 8.25 5.25V12" />
                            </svg>
                            <p class="text-lg">Email: contact@mef.gouv.ht</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-900" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5h6M5 9h14M5 13h14M5 17h14" />
                            </svg>
                            <p class="text-lg">Address: Port-au-Prince, Haiti</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-blue-900">Send Us a Message</h2>
                    <form class="mt-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="first-name" class="block text-lg font-medium">First Name</label>
                                <input type="text" id="first-name" name="first-name" required
                                    class="mt-2 block w-full rounded-md border border-gray-300 shadow-sm focus:ring-blue-600 focus:border-blue-600 p-2.5">
                            </div>
                            <div>
                                <label for="last-name" class="block text-lg font-medium">Last Name</label>
                                <input type="text" id="last-name" name="last-name" required
                                    class="mt-2 block w-full rounded-md border border-gray-300 shadow-sm focus:ring-blue-600 focus:border-blue-600 p-2.5">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-lg font-medium">Email</label>
                            <input type="email" id="email" name="email" required
                                class="mt-2 block w-full rounded-md border border-gray-300 shadow-sm focus:ring-blue-600 focus:border-blue-600 p-2.5">
                        </div>

                        <div>
                            <label for="message" class="block text-lg font-medium">Message</label>
                            <textarea id="message" name="message" rows="4" required
                                class="mt-2 block w-full rounded-md border border-gray-300 shadow-sm focus:ring-blue-600 focus:border-blue-600 p-2.5"></textarea>
                        </div>

                        <div>
                            <button type="submit"
                                class="w-full py-2 px-4 bg-blue-900 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50">Send
                                Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Google Map Section -->
    <div class="max-w-7xl mx-auto px-6 text-center mb-5">
        <iframe class="w-full h-96 rounded-lg shadow-lg"
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387190.2799124901!2d-74.25986799234231!3d40.69767006391955!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c258e5f8a4e4fb%3A0x3cb3e66dbaba8e6c!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2s!4v1637356978689!5m2!1sen!2s"
            allowfullscreen="" loading="lazy"></iframe>
    </div>
@endsection
