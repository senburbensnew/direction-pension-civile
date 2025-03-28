<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <div class="flex items-center justify-center h-screen">
        <div class="text-center px-4">
            <!-- SVG Icon -->
            <div class="mx-auto w-24 h-24 md:w-32 md:h-32 lg:w-48 lg:h-48 text-[#FFD700] animate-bounce">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-full h-full">
                    <path fill-rule="evenodd"
                        d="M12 2a10 10 0 100 20 10 10 0 000-20zm-1 5a1 1 0 012 0v6a1 1 0 01-2 0V7zm1 10a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <!-- Message -->
            <h1 class="text-6xl md:text-8xl lg:text-9xl font-bold text-[#173152] mt-6">403</h1>
            <p class="text-base md:text-lg lg:text-xl text-gray-300 mt-2">{{ __('errors.permission_denied') }}</p>
            <a href="{{ route('home') }}"
                class="mt-6 inline-block px-6 py-3 bg-[#FFD700] text-[#173152] rounded-lg hover:bg-[#FFC107] transition duration-300 font-semibold hover:scale-105">{{ __('errors.back_to_home') }}</a>
        </div>
    </div>
</body>

</html>
