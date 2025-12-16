@extends('layouts.main')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $report->title }}</h1>
            <p class="text-gray-600">Année: {{ $report->year }}</p>
            <p class="text-sm mt-2">{{ $report->description }}</p>
        </div>
        <div>
            <a href="{{ route('reports.download', $report) }}" class="px-4 py-2 bg-blue-600 text-white rounded">Télécharger</a>
        </div>
    </div>

    <div class="mt-6">
        @if(str_contains($report->mime_type, 'pdf'))
            <!-- Affichage PDF dans iframe -->

<iframe src="{{ route('reports.view', $report) }}" class="w-full h-[700px] border" frameborder="0"></iframe>


        @else
            <p class="text-gray-500 text-center">Prévisualisation non disponible pour ce format. Téléchargez le fichier pour le consulter.</p>
        @endif
    </div>
</div>
@endsection
