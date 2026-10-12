@extends('layouts.admin')

@section('title', 'Demandes de compte')
@section('breadcrumb')
    <span class="text-gray-700 text-sm">Demandes de compte</span>
@endsection

@section('content')
<div class="dossier-page space-y-6">
    <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-indigo-600">
        <p class="dossier-kicker">Administration</p>
        <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">Demandes de création de compte</h1>
        <p class="mt-2 text-sm text-gray-600 max-w-2xl">Examen et décision des dossiers d’ouverture de compte.</p>
    </header>
    @include('comptes-demandes._list', ['routePrefix' => 'admin'])
</div>
@endsection
