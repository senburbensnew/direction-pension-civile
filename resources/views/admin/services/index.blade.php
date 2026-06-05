@extends('layouts.admin')

@section('title', 'Services')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Services</span>
@endsection

@section('content')
<div class="space-y-4" x-data="{ showCreate: false, editService: null }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Services</h1>
            <p class="text-sm text-gray-500 mt-0.5">Unités organisationnelles de la direction.</p>
        </div>
        <button type="button" @click="showCreate = true"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-plus"></i> Nouveau service
        </button>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">#</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Code</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Icône</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Nom du service</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Description</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Agents affectés</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $index => $service)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-mono px-2 py-0.5 bg-slate-100 text-slate-600 rounded">
                                {{ $service->code ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($service->icon)
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-{{ $service->color ?? 'gray' }}-100">
                                <i class="fas {{ $service->icon }} text-{{ $service->color ?? 'gray' }}-600 text-sm"></i>
                            </span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $service->nom }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs max-w-xs">
                            {{ $service->description ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <span class="w-6 h-6 rounded-full bg-teal-100 flex items-center justify-center">
                                    <i class="fas fa-user text-teal-600 text-xs"></i>
                                </span>
                                <span class="text-sm font-medium text-gray-700">{{ $service->users->count() }}</span>
                                <span class="text-xs text-gray-400">agent{{ $service->users->count() > 1 ? 's' : '' }}</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-sitemap text-3xl mb-2 block"></i>
                            Aucun service défini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($services->hasPages())
        <div>{{ $services->links() }}</div>
    @endif

    {{-- Modal création --}}
    <div x-show="showCreate" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6" @click.stop>
            <h3 class="font-semibold text-gray-800 text-base mb-4">
                <i class="fas fa-sitemap mr-2 text-teal-500"></i> Nouveau service
            </h3>
            <p class="text-sm text-gray-500 mb-4">
                La création de services via l'interface est en cours de développement.
                Les services sont actuellement définis via les migrations et seeders.
            </p>
            <div class="flex justify-end">
                <button type="button" @click="showCreate = false"
                    class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                    Fermer
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
