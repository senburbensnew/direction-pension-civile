@extends('layouts.main')

@section('title', 'Actualités pour les Retraités')

@section('content')
<section class="py-12 bg-white fade-in">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold mb-8 text-center gradient-text">
            Actualités pour les Retraités
        </h2>

        <div class="grid md:grid-cols-3 gap-6">
            @foreach ($actualites as $actu)
                <div class="bg-gray-50 rounded-lg shadow p-6 hover:shadow-lg transition">
                    @if($actu->image)
                        <img src="{{ asset('storage/' . $actu->image) }}" alt="{{ $actu->title }}" class="rounded mb-4 w-full h-40 object-cover">
                    @endif
                    <h3 class="text-xl font-semibold mb-2">{{ $actu->title }}</h3>
                    <p class="text-gray-700 mb-4">{{ Str::limit($actu->description, 100) }}</p>
                    <a href="{{ route('actualites.show', $actu->id) }}" class="text-blue-600 font-medium hover:underline">
                        Lire la suite
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $actualites->links() }} <!-- pagination -->
        </div>
    </div>
</section>
@endsection
