@props(['nouveautes' => []])

@php
    if (empty($nouveautes)) {
        $nouveautes = [
            [
                'title' => 'Formulaires de pension simplifiés',
                'date' => '1 Nov 2025',
                'content' => 'Vos démarches administratives n’ont jamais été aussi simples ! Remplissez et soumettez vos formulaires en ligne facilement.',
                'icon' => 'fas fa-file-alt',
                'color' => 'bg-blue-100 text-blue-600',
            ],
            [
                'title' => 'Simulateur de pension amélioré',
                'date' => '10 Nov 2025',
                'content' => 'Estimez votre retraite avec précision grâce à notre nouveau simulateur interactif et intuitif.',
                'icon' => 'fas fa-calculator',
                'color' => 'bg-orange-100 text-orange-600',
            ],
            [
                'title' => 'Nouvelle rubrique FAQ',
                'date' => '20 Nov 2025',
                'content' => 'Toutes vos questions fréquentes sont désormais regroupées pour vous guider pas à pas.',
                'icon' => 'fas fa-question-circle',
                'color' => 'bg-purple-100 text-purple-600',
            ],
        ];
    }
@endphp

<div class="w-full mt-6 md:mt-0 overflow-hidden">

    <div class="flex flex-wrap justify-center items-stretch gap-6 p-4">
        @foreach ($nouveautes as $nouveaute)
            <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition duration-300 flex flex-col w-72">

                <div class="flex items-center justify-center h-16 w-16 rounded-full mx-auto mt-4 {{ $nouveaute['color'] }}">
                    <i class="{{ $nouveaute['icon'] }} text-2xl"></i>
                </div>

                <div class="p-4 flex flex-col flex-1">
                    <h3 class="text-lg font-bold mb-2 text-gray-800">{{ $nouveaute['title'] }}</h3>
                    <span class="text-sm text-gray-400 mb-3">{{ $nouveaute['date'] }}</span>
                    <p class="text-gray-600 flex-1">{{ $nouveaute['content'] }}</p>
                    <a href="#" class="mt-4 text-blue-600 font-semibold hover:text-blue-800 transition-colors self-start">
                        Lire plus →
                    </a>
                </div>

            </div>
        @endforeach
    </div>

</div>
