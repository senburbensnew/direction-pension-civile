@props([
    'actualites',
    'showLatest' => false
])

    @if($actualites->isEmpty())
        <p class="text-center text-gray-500 text-lg">
            Aucune actualité pour le moment. Revenez plus tard !
        </p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($actualites as $actu)
                <x-actualite-card :actualite="$actu" />
            @endforeach
        </div>
        @if(!$showLatest)
            <div class="mt-8">
                {{ $actualites->links() }} <!-- pagination -->
            </div>
        @endif
    @endif