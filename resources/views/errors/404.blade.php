<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <div class="flex items-center justify-center h-screen">
        <div class="text-center px-4">
            <!-- SVG Icon -->
            <div class="mx-auto w-24 h-24 md:w-32 md:h-32 lg:w-48 lg:h-48 text-[#FFD700] animate-bounce">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    class="w-full h-full">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- 404 Text -->
            <h1 class="text-6xl md:text-8xl lg:text-9xl font-bold text-[#173152] mt-6">404</h1>
            <p class="text-xl md:text-2xl lg:text-3xl text-[#FFD700] mt-4">{{ __('errors.page_not_found') }}</p>
            <p class="text-base md:text-lg lg:text-xl text-gray-300 mt-2">
                {{ __('errors.page_not_found_description') }}
            </p>

            <!-- Go Back Home Button -->
            {{--             <a href="{{ route('home') }}"
                class="mt-6 inline-block px-6 py-3 bg-[#FFD700] text-[#173152] rounded-lg hover:bg-[#FFC107] transition duration-300 font-semibold hover:scale-105">
                {{ __('errors.back_to_home') }}
            </a> --}}
        </div>
    </div>
    @vite(['resources/js/app.js'])
</body>

</html>
