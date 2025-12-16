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
                <x-actualite-card :actualite="$actu" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $actualites->links() }} <!-- pagination -->
        </div>
    </div>
</section>
@endsection
