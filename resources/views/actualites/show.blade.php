@extends('layouts.main')

@section('title', $actu->title)

@section('content')
<section class="py-12 bg-white fade-in">
    <div class="container mx-auto px-4 max-w-3xl">
        <h1 class="text-3xl font-bold mb-6">{{ $actu->title }}</h1>

        @if($actu->image)
            <img src="{{ asset('storage/' . $actu->image) }}" alt="{{ $actu->title }}" class="rounded mb-6 w-full h-64 object-cover">
        @endif

        <p class="text-gray-700 mb-4">{{ $actu->description }}</p>

        <a href="{{ route('actualites.index') }}" class="text-blue-600 font-medium hover:underline">
            ← Retour aux actualités
        </a>
    </div>
</section>
@endsection
