@extends('layouts.main')

@section('title', 'Réabonnement newsletter')

@section('content')
<div class="site-gutter py-16 max-w-md mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Se réabonner</h1>
    <p class="text-gray-600 text-sm mb-6">Si vous vous étiez désabonné, confirmez votre adresse pour reprendre la newsletter.</p>

    <form method="POST" action="{{ route('newsletter.resubscribe.store') }}" class="space-y-3">
        @csrf
        <input type="email" name="email" required value="{{ old('email') }}" placeholder="votre@email.com"
               class="w-full border border-gray-300 rounded-lg px-3 py-2">
        @error('email')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
        <button type="submit" class="w-full py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg">Réabonner</button>
    </form>
</div>
@endsection
