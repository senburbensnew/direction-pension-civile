@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6 max-w-2xl">

    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-300 text-red-700 rounded-lg px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">
            {{ isset($reportEdit) ? 'Éditer le rapport' : 'Ajouter un rapport' }}
        </h1>
        <a href="{{ route('admin.reports.admin.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Retour à la liste
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ isset($reportEdit) ? route('admin.reports.update', $reportEdit->id) : route('admin.reports.store') }}"
              method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if(isset($reportEdit))
                @method('PUT')
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Titre <span class="text-red-500">*</span>
                </label>
                <input name="title"
                    value="{{ old('title', $reportEdit->title ?? '') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                <input name="year" type="number"
                    value="{{ old('year', $reportEdit->year ?? '') }}"
                    min="1900" max="{{ date('Y') + 1 }}"
                    placeholder="{{ date('Y') }}"
                    class="w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $reportEdit->description ?? '') }}</textarea>
            </div>

            <div>
                <x-file-input name="file" label="Fichier PDF" accept=".pdf"
                    :required="!isset($reportEdit)"
                    hint="PDF uniquement — max 20 Mo" />

                @if(isset($reportEdit) && $reportEdit->file_path)
                    <div class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                        <i class="fas fa-file-pdf text-red-500"></i>
                        Fichier actuel :
                        <a href="{{ Storage::url($reportEdit->file_path) }}" target="_blank"
                            class="text-blue-600 hover:underline">
                            {{ $reportEdit->file_name }}
                        </a>
                        <span class="text-gray-400">({{ round($reportEdit->file_size / 1024) }} Ko)</span>
                    </div>
                @endif
            </div>

            <div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="status" value="published" class="rounded"
                        {{ (old('status', $reportEdit->status ?? '') === 'published') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Publier immédiatement</span>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
                    {{ isset($reportEdit) ? 'Mettre à jour' : 'Enregistrer' }}
                </button>
                <a href="{{ route('admin.reports.admin.index') }}"
                    class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
