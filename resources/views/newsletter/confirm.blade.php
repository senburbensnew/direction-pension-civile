@extends('layouts.main')

@section('title', 'Confirmation newsletter')

@section('content')
<div class="site-gutter py-16 max-w-lg mx-auto text-center">
    @if($status === 'success')
        <div class="text-green-600 text-5xl mb-4"><i class="fas fa-check-circle"></i></div>
    @else
        <div class="text-amber-500 text-5xl mb-4"><i class="fas fa-exclamation-circle"></i></div>
    @endif
    <h1 class="text-2xl font-bold text-gray-800 mb-3">Newsletter</h1>
    <p class="text-gray-600">{{ $message }}</p>
    <a href="{{ route('home') }}" class="inline-block mt-6 text-blue-600 hover:underline">Retour à l’accueil</a>
</div>
@endsection
