@extends('layouts.admin')

@section('title', 'Statuts des demandes')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Statuts des demandes</span>
@endsection

@section('content')
<div class="space-y-4" x-data="{ showCreate: false, editStatus: null }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Statuts des demandes</h1>
            <p class="text-sm text-gray-500 mt-0.5">Libellés et descriptions des états métier. La localisation est gérée par les étapes de workflow.</p>
        </div>
        <button type="button" @click="showCreate = true"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-plus"></i> Nouvel état
        </button>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-2 bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">#</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Code</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Libellé</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Description</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Demandes</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Nœuds du circuit</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($statuses as $index => $status)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-mono px-2 py-1 bg-slate-100 text-slate-600 rounded">
                                {{ $status->code }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @php $style = \App\Models\Etat::getStatusStyle($status->code); @endphp
                                {{ $style }}">
                                {{ $status->label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">
                            {{ $status->description ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full
                                {{ $status->demandes_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }}
                                text-xs font-semibold">
                                {{ $status->demandes_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($status->workflow_steps_count > 0)
                                <a href="{{ route('admin.flux-transitions.index') }}"
                                   class="inline-flex items-center justify-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold hover:bg-indigo-200 transition-colors">
                                    <i class="fas fa-circle text-[7px]"></i> {{ $status->workflow_steps_count }}
                                </a>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                    @click="editStatus = {{ $status->toJson() }}"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                @if($status->demandes_count === 0)
                                    <form method="POST" action="{{ route('admin.etats.destroy', $status) }}"
                                          onsubmit="return confirm('Supprimer cet état ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-tag text-3xl mb-2 block"></i>
                            Aucun état défini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal création --}}
    <div x-show="showCreate" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @keydown.escape.window="showCreate = false">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6" @click.stop>
            <h3 class="font-semibold text-gray-800 text-base mb-4">
                <i class="fas fa-plus-circle mr-2 text-blue-500"></i> Nouvel état
            </h3>
            <form method="POST" action="{{ route('admin.etats.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required
                        placeholder="ex. EN_REVISION"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none uppercase"
                        oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '')">
                    <p class="text-xs text-gray-400 mt-1">Lettres majuscules, chiffres et underscores uniquement.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Libellé <span class="text-red-500">*</span></label>
                    <input type="text" name="label" required maxlength="120"
                        placeholder="ex. En révision"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" maxlength="500"
                        placeholder="Description optionnelle visible par les agents"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="showCreate = false"
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal édition --}}
    <div x-show="editStatus !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @keydown.escape.window="editStatus = null">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6" @click.stop>
            <h3 class="font-semibold text-gray-800 text-base mb-1">
                <i class="fas fa-pencil-alt mr-2 text-blue-500"></i> Modifier l'état
            </h3>
            <p class="text-xs text-gray-400 mb-4" x-text="'Code : ' + (editStatus?.code ?? '')"></p>

            <template x-if="editStatus">
                <form method="POST"
                      :action="'{{ url('admin/etats') }}/' + editStatus.id"
                      class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PATCH">

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Libellé <span class="text-red-500">*</span></label>
                        <input type="text" name="label" required maxlength="120"
                            :value="editStatus.label"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="2" maxlength="500"
                            x-text="editStatus.description ?? ''"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none resize-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="editStatus = null"
                            class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                            Annuler
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
