@extends('layouts.main')

@section('title', 'Désabonnement Newsletter')

@section('content')
<div class="py-16 px-4">
    <div class="max-w-md mx-auto text-center">

        @if($status === 'success')
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-3">Désabonnement confirmé</h1>
            <p class="text-gray-600 mb-6">
                Votre adresse email a été retirée de notre liste. Vous ne recevrez plus de newsletters de notre part.
            </p>

        @elseif($status === 'invalid')
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-times text-red-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-3">Lien invalide</h1>
            <p class="text-gray-600 mb-6">
                Ce lien de désabonnement est invalide ou a déjà été utilisé.
            </p>
        @endif

        <a href="{{ route('home') }}"
            class="inline-block px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
            Retour à l'accueil
        </a>
    </div>
</div>
@endsection
