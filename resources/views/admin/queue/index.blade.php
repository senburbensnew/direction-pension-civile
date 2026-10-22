@extends('layouts.admin')

@section('title', 'File d’attente')
@section('breadcrumb')
    <a href="{{ route('admin.settings') }}" class="text-gray-500 hover:text-gray-800">Paramètres</a>
    <i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i>
    <span class="text-gray-800">File d’attente</span>
@endsection

@section('content')
    <div class="site-gutter w-full space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-800 mb-1">Jobs mail / SMS / notifications</h1>
                <p class="text-sm text-gray-500 max-w-3xl">
                    Jobs en attente (worker arrêté ou surcharge) et jobs en échec après {{ $maxTries }} tentatives.
                    Driver actuel :
                    <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $driver }}</code>
                </p>
            </div>
            @if($supportsDatabaseMonitor && $failedCount > 0)
                <form method="POST" action="{{ route('admin.queue.failed.retry-all') }}"
                      onsubmit="return confirm('Remettre tous les jobs en échec dans la file ?');">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold rounded-lg bg-[#173052] text-white hover:bg-orange-600">
                        Tout réessayer ({{ $failedCount }})
                    </button>
                </form>
            @endif
        </div>

        @if(! $supportsDatabaseMonitor)
            <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-lg px-4 py-3 text-sm">
                <p class="font-medium mb-1">Monitoring indisponible</p>
                <p>Le driver <strong>{{ $driver }}</strong> ne stocke pas les jobs en base. Passez
                    <code class="text-xs">QUEUE_CONNECTION=database</code> et lancez
                    <code class="text-xs">php artisan queue:work</code> pour activer cette vue.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">En attente / en cours</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $pendingCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Table <code class="text-[11px]">jobs</code></p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">En échec</p>
                    <p class="text-2xl font-bold {{ $failedCount > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">{{ $failedCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Table <code class="text-[11px]">failed_jobs</code></p>
                </div>
            </div>

            @if($pendingCount > 0 && $pendingJobs->where('status', 'pending')->count() === $pendingCount)
                <div class="bg-sky-50 border border-sky-200 text-sky-900 rounded-lg px-4 py-3 text-sm">
                    <i class="fas fa-info-circle mr-1"></i>
                    Tous les jobs listés sont encore en attente — vérifiez qu’un worker tourne :
                    <code class="text-xs bg-white/80 px-1 rounded">php artisan queue:work</code>
                </div>
            @endif

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">En attente (100 derniers)</span>
                </div>
                @if($pendingJobs->isEmpty())
                    <p class="p-5 text-sm text-gray-500">Aucun job en file.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-2">ID</th>
                                    <th class="px-4 py-2">Job</th>
                                    <th class="px-4 py-2">File</th>
                                    <th class="px-4 py-2">Statut</th>
                                    <th class="px-4 py-2">Tentatives</th>
                                    <th class="px-4 py-2">Créé</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($pendingJobs as $job)
                                    <tr class="hover:bg-gray-50/80">
                                        <td class="px-4 py-2 font-mono text-xs">{{ $job->id }}</td>
                                        <td class="px-4 py-2">
                                            <span class="font-medium text-gray-800">{{ $job->job_label }}</span>
                                            @if($job->job_class)
                                                <div class="text-[11px] text-gray-400 truncate max-w-xs" title="{{ $job->job_class }}">{{ $job->job_class }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">{{ $job->queue }}</td>
                                        <td class="px-4 py-2">
                                            @if($job->status === 'processing')
                                                <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-xs">En cours</span>
                                            @else
                                                <span class="text-sky-700 bg-sky-50 px-2 py-0.5 rounded text-xs">En attente</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">{{ $job->attempts }}</td>
                                        <td class="px-4 py-2 text-gray-600 whitespace-nowrap">{{ $job->created_at_label }}</td>
                                        <td class="px-4 py-2 text-right">
                                            <form method="POST" action="{{ route('admin.queue.pending.destroy', $job->id) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Supprimer ce job de la file ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-800">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Échecs (100 derniers)</span>
                </div>
                @if($failedJobs->isEmpty())
                    <p class="p-5 text-sm text-gray-500">Aucun job en échec.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-2">Job</th>
                                    <th class="px-4 py-2">File</th>
                                    <th class="px-4 py-2">Échec</th>
                                    <th class="px-4 py-2">Erreur</th>
                                    <th class="px-4 py-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($failedJobs as $job)
                                    <tr class="hover:bg-gray-50/80">
                                        <td class="px-4 py-2">
                                            <span class="font-medium text-gray-800">{{ $job->job_label }}</span>
                                            @if($job->job_class)
                                                <div class="text-[11px] text-gray-400 truncate max-w-xs" title="{{ $job->job_class }}">{{ $job->job_class }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">{{ $job->queue }}</td>
                                        <td class="px-4 py-2 text-gray-600 whitespace-nowrap">{{ $job->failed_at }}</td>
                                        <td class="px-4 py-2 text-gray-600 max-w-md truncate" title="{{ $job->exception_preview }}">{{ $job->exception_preview }}</td>
                                        <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                            <a href="{{ route('admin.queue.failed.show', $job->uuid) }}" class="text-xs text-indigo-600 hover:text-indigo-800">Détail</a>
                                            <form method="POST" action="{{ route('admin.queue.failed.retry', $job->uuid) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-800">Réessayer</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.queue.failed.forget', $job->uuid) }}" class="inline"
                                                  onsubmit="return confirm('Retirer cette entrée des échecs ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-800">Oublier</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
