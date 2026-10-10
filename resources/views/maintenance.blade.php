<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="maintenance-page"
    data-theme="light"
>
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>Maintenance — Direction de la Pension Civile</title>

    <x-fonts />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >
</head>

<body class="site-public min-h-screen bg-gray-50 font-sans antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-6">

        <div class="w-full max-w-md">

            {{-- Carte principale --}}
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-lg shadow-gray-900/5">

                {{-- Barre institutionnelle --}}
                <div class="h-1 bg-[#0a4f86]"></div>

                <div class="px-6 py-7 sm:px-8">

                    {{-- Logo + institution --}}
                    <div class="mb-5 flex flex-col items-center">

                        <a
                            href="{{ url('/') }}"
                            title="Retour à l'accueil"
                            class="group"
                        >
                            <img
                                src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                                class="h-14 w-14 object-contain transition-opacity duration-200 group-hover:opacity-80"
                                alt="Logo DPC"
                            >
                        </a>

                        <p class="mt-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Direction de la Pension Civile
                        </p>

                    </div>


                    {{-- Badge maintenance --}}
                    <div class="mb-4 flex justify-center">

                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">

                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>

                            Maintenance en cours

                        </span>

                    </div>


                    {{-- Message principal --}}
                    <div class="text-center">

                        <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                            Site temporairement indisponible
                        </h1>

                        <p class="mt-3 text-sm leading-6 text-gray-500">
                            Nous effectuons actuellement des travaux de maintenance.
                            Le portail sera bientôt de nouveau disponible.
                        </p>

                    </div>


                    {{-- Contact --}}
                    <div class="mt-6 rounded-xl bg-gray-50 px-4 py-3.5">

                        <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2">

                            <a
                                href="mailto:dpc.info@mef.gouv.ht"
                                class="inline-flex items-center gap-2 text-xs text-gray-500 transition-colors hover:text-[#0a4f86]"
                            >
                                <i class="fa-solid fa-envelope text-[#0a4f86]"></i>
                                dpc.info@mef.gouv.ht
                            </a>

                            <a
                                href="tel:+50929921007"
                                class="inline-flex items-center gap-2 text-xs text-gray-500 transition-colors hover:text-[#0a4f86]"
                            >
                                <i class="fa-solid fa-phone text-[#0a4f86]"></i>
                                +509 29 92 1007
                            </a>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Copyright --}}
            <p class="mt-4 text-center text-[11px] text-gray-400">
                &copy; {{ date('Y') }}
                Direction de la Pension Civile — République d'Haïti
            </p>

        </div>

    </main>
</body>
</html>