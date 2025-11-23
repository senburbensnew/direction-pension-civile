@props(['nouveautes' => []])

@php
    // Nouveautés par défaut si aucune n'est passée
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
                'title' => 'Atelier de sensibilisation',
                'date' => '15 Nov 2025',
                'content' => 'Participez à notre atelier gratuit et découvrez comment optimiser vos droits à la pension civile.',
                'icon' => 'fas fa-chalkboard-teacher',
                'color' => 'bg-green-100 text-green-600',
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
{{--     <div class="gradient-bg text-white fade-in text-center py-3 text-lg font-bold tracking-wide rounded-t-lg">
        {{ __('messages.new_feeds') }}
    </div> --}}

    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($nouveautes as $nouveaute)
            <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition duration-300 flex flex-col">
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
