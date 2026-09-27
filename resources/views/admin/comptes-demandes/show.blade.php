@extends('layouts.admin')

@section('title', 'Demande de compte '.$demande->code)
@section('breadcrumb')
    <a href="{{ route('admin.comptes-demandes.index') }}" class="text-gray-500 text-sm hover:underline">Demandes de compte</a>
    <span class="text-gray-400 mx-1">/</span>
    <span class="text-gray-700 text-sm">{{ $demande->code }}</span>
@endsection

@section('content')
<div class="space-y-6">
    @include('comptes-demandes._detail')
</div>
@endsection
