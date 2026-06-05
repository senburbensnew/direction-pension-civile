@extends('layouts.main')

@section('title', 'FAQ')

@section('content')
    <style>
        .gradient-bg { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); }
        .gradient-text { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-shadow { box-shadow: 0 20px 40px -10px rgba(0,0,0,.1), 0 10px 20px -5px rgba(0,0,0,.04); }
        .faq-card { transition: all 0.3s ease; }
        .faq-card:hover { transform: translateY(-3px); box-shadow: 0 20px 40px -12px rgba(0,0,0,.12); }
        .faq-content { display: none; }
        .fade-in { animation: fadeIn 0.6s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <section class="py-8 relative fade-in">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-bold gradient-text mb-3">Foire Aux Questions</h1>
                <p class="text-gray-600 text-lg">Retrouvez les réponses aux questions les plus fréquentes.</p>
            </div>

            @if($items->isEmpty())
                <div class="bg-white rounded-2xl card-shadow p-12 text-center text-gray-400">
                    <i class="fas fa-question-circle text-4xl mb-3 block"></i>
                    <p>Aucune question disponible pour le moment.</p>
                </div>
            @else
                @foreach($items as $category => $categoryItems)
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-700 mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-folder text-blue-600 text-sm"></i>
                            </span>
                            {{ $category }}
                        </h2>

                        <div class="space-y-3">
                            @foreach($categoryItems as $faq)
                                <div class="bg-white border border-gray-200 rounded-xl p-6 faq-card">
                                    <button class="w-full flex justify-between items-center text-left text-base font-semibold text-gray-800 hover:text-blue-700 transition-colors"
                                        onclick="toggleFAQ(this)">
                                        {{ $faq->question }}
                                        <i class="fas fa-plus text-gray-400 flex-shrink-0 ml-4"></i>
                                    </button>
                                    <div class="faq-content mt-4 text-gray-600 leading-relaxed">
                                        {!! nl2br(e($faq->answer)) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="mt-8 bg-blue-50 border border-blue-100 rounded-xl p-6 text-center">
                <p class="text-gray-700 mb-3">Vous ne trouvez pas de réponse à votre question ?</p>
                <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                    <i class="fas fa-envelope"></i> Nous contacter
                </a>
            </div>
        </div>
    </section>

    <script>
        function toggleFAQ(btn) {
            const content = btn.nextElementSibling;
            const icon = btn.querySelector("i");
            if (content.style.display === "block") {
                content.style.display = "none";
                icon.classList.replace("fa-minus", "fa-plus");
            } else {
                content.style.display = "block";
                icon.classList.replace("fa-plus", "fa-minus");
            }
        }
    </script>
@endsection
