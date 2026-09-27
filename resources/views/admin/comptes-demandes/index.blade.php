@extends('layouts.admin')

@section('title', 'Demandes de compte')
@section('breadcrumb')
    <span class="text-gray-700 text-sm">Demandes de compte</span>
@endsection

@section('content')
<div class="space-y-6">
    @include('comptes-demandes._list')
</div>
@endsection
