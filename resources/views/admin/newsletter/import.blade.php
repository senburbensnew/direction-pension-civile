@extends('layouts.admin')

@section('title', 'Importer des abonnés')

@section('content')
<div class="max-w-xl mx-auto bg-white border border-gray-200 rounded-xl p-6 space-y-4">
    <h1 class="text-lg font-bold text-gray-800">Importer des abonnés (CSV)</h1>
    <p class="text-sm text-gray-600">Colonnes requises : <code>email</code>, <code>consent</code> (oui/1/true). Optionnel : <code>locale</code>, <code>audience_group</code>.</p>

    <form method="POST" action="{{ route('admin.newsletter.import.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="file" name="file" accept=".csv,text/csv" required class="w-full text-sm">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="require_consent" value="1" checked>
            Exiger la colonne « consent »
        </label>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg">Importer</button>
    </form>
</div>
@endsection
