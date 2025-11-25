<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @stack('meta')
    <title>@yield('title', 'Direction de la Pension Civile')</title>
    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    <link href="{{ asset('build/assets/app-CFGfTGFn.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body class="bg-gray-100">
    <x-header />
    <main class="container mx-auto px-4 py-6 min-h-screen">
        @yield('content')
    </main>
    <x-footer />
    <x-contact-info-bar borderType="top"  />
    @stack('scripts')
    <script src="{{ asset('build/assets/app-CbEvcXly.js') }}"></script>
</body>
</html>
