@extends('layouts.admin')

@section('title', 'Job en échec')
@section('breadcrumb')
    <a href="{{ route('admin.settings') }}" class="text-gray-500 hover:text-gray-800">Paramètres</a>
    <i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i>
    <a href="{{ route('admin.queue.index') }}" class="text-gray-500 hover:text-gray-800">File d’attente</a>
    <i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i>
    <span class="text-gray-800">Échec</span>
@endsection

@section('content')
    <div class="site-gutter w-full space-y-6 max-w-5xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">{{ $parsed['label'] }}</h1>
                @if($parsed['class'])
                    <p class="text-xs text-gray-500 font-mono mt-1 break-all">{{ $parsed['class'] }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.queue.failed.retry', $job->uuid) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                        Réessayer
                    </button>
                </form>
                <a href="{{ route('admin.queue.index') }}" class="px-4 py-2 text-sm rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50">
                    Retour
                </a>
            </div>
        </div>

        <dl class="bg-white border border-gray-200 rounded-xl divide-y divide-gray-100 text-sm">
            <div class="px-5 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1">
                <dt class="text-gray-500">UUID</dt>
                <dd class="sm:col-span-2 font-mono text-xs break-all">{{ $job->uuid }}</dd>
            </div>
            <div class="px-5 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1">
                <dt class="text-gray-500">File</dt>
                <dd class="sm:col-span-2">{{ $job->queue }}</dd>
            </div>
            <div class="px-5 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1">
                <dt class="text-gray-500">Échec le</dt>
                <dd class="sm:col-span-2">{{ $job->failed_at }}</dd>
            </div>
        </dl>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                Exception
            </div>
            <pre class="p-5 text-xs text-red-900 bg-red-50/50 overflow-x-auto whitespace-pre-wrap break-words max-h-[480px] overflow-y-auto">{{ $job->exception }}</pre>
        </div>
    </div>
@endsection
