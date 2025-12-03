<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @stack('meta')
    <title>@yield('title', 'Direction de la Pension Civile')</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <link href="{{ asset('build/assets/app-CFGfTGFn.css') }}" rel="stylesheet"> --}}
    @stack('styles')
</head>

<body class="bg-gray-100 text-gray-800">
    <x-header />
    <main class="container mx-auto px-4 min-h-screen">
        @yield('content')
    </main>
    <x-footer />
    <x-contact-info-bar borderType="top"  />
    @stack('scripts')
    <script src="{{ asset('build/assets/app-CbEvcXly.js') }}"></script>
</body>
</html>
