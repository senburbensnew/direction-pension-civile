@extends('layouts.admin')

@section('title', 'Utilisateurs')
@section('breadcrumb')
    <span class="text-gray-700 text-sm">Utilisateurs</span>
@endsection

@section('content')
@php
    $exportQuery = array_merge(
        request()->only(['q', 'role', 'sort', 'dir']),
        ['columns' => $selectedColumns],
    );
    $tableColumnCount = count($selectedColumns) + 1 + ($sessionsManageable ? 1 : 0);
    $columnLabels = collect($columnCatalog->definitions())
        ->mapWithKeys(fn (array $definition, string $key) => [$key => $definition['label']])
        ->all();
    $sortableColumns = $columnCatalog->sortableKeys();
@endphp
<div class="space-y-4 min-w-0 max-w-full">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl font-bold text-gray-800">Utilisateurs</h1>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.users.export', $exportQuery) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-file-csv text-green-600"></i> {{ __('actions.export') }}
            </a>
            <a href="{{ route('admin.users.create') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-user-plus"></i> Créer un utilisateur
            </a>
        </div>
    </div>

    <div
        class="space-y-3"
        data-admin-users-index
        x-data="adminUsersIndex(@js([
            'columns' => $selectedColumns,
            'labels' => $columnLabels,
            'sortable' => $sortableColumns,
            'defaultDir' => $columnDefaultDirections,
            'sort' => $displaySortColumn,
            'dir' => $displaySortDirection,
            'explicitSort' => $sortColumn !== null,
            'indexUrl' => route('admin.users.index'),
        ]))"
    >
    {{-- Filters --}}
    <form method="GET" class="space-y-3" @submit="startLoading()">
        @foreach($selectedColumns as $columnKey)
            <input type="hidden" name="columns[]" value="{{ $columnKey }}">
        @endforeach
        @if($sortColumn)
            <input type="hidden" name="sort" value="{{ $sortColumn }}">
            <input type="hidden" name="dir" value="{{ $sortDirection }}">
        @endif

        <div class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}"
                placeholder="Rechercher par nom ou email…"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <select name="role"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">tous les rôles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                        {{ strtolower($role->name) }}
                    </option>
                @endforeach
            </select>

            <button type="submit"
                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg">
                <i class="fas fa-search mr-1"></i> Filtrer
            </button>

            @if(request('q') || request('role') || request()->has('columns'))
                <a href="{{ route('admin.users.index') }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm rounded-lg">
                    <i class="fas fa-times mr-1"></i> Effacer
                </a>
            @endif
        </div>

    </form>

    {{-- Column picker --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-3" @submit="startLoading()">
        @if(request('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
        @endif
        @if(request('role'))
            <input type="hidden" name="role" value="{{ request('role') }}">
        @endif
        @if($sortColumn)
            <input type="hidden" name="sort" value="{{ $sortColumn }}">
            <input type="hidden" name="dir" value="{{ $sortDirection }}">
        @endif

        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-sm font-medium text-gray-800">Colonnes du tableau et de l’export</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    Cochez les colonnes à afficher. Réordonnez et triez directement dans le tableau.
                </p>
            </div>
            <button type="button"
                @click="showColumns = !showColumns"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
                <i class="fas fa-columns text-indigo-500"></i>
                Personnaliser
                <i class="fas fa-chevron-down text-xs transition-transform" :class="showColumns ? 'rotate-180' : ''"></i>
            </button>
        </div>

        <div x-show="showColumns" x-cloak x-transition class="pt-4 border-t border-gray-100">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
                @foreach($columnCatalog->definitions() as $columnKey => $columnDefinition)
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input
                            type="checkbox"
                            name="columns[]"
                            value="{{ $columnKey }}"
                            @checked(in_array($columnKey, $selectedColumns, true))
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <span>{{ $columnDefinition['label'] }}</span>
                    </label>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg">
                    Appliquer les colonnes
                </button>
                <a href="{{ route('admin.users.index', request()->only(['q', 'role'])) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">
                    Réinitialiser
                </a>
            </div>
        </div>
    </form>

    <div
        class="relative w-full min-w-0 max-w-full overflow-x-auto overscroll-x-contain bg-white rounded-xl border border-gray-200 shadow-sm min-h-[12rem]"
        x-init="measureTableHead()"
        @resize.window="measureTableHead()"
        :class="loading ? 'pointer-events-none' : ''"
        :aria-busy="loading ? 'true' : 'false'"
    >
        <div
            x-show="loading"
            x-cloak
            x-transition:enter="transition-opacity duration-300 ease-out"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="dpc-users-table-load-bar absolute inset-x-0 z-[15] pointer-events-none"
            :style="`top: ${Math.max(theadOffset - 1, 0)}px`"
            aria-hidden="true"
        ></div>
        <div
            x-show="loading"
            x-cloak
            x-transition:enter="transition-opacity duration-300 ease-out"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="absolute inset-x-0 bottom-0 z-10 bg-slate-900/[0.03] pointer-events-none"
            :style="`top: ${theadOffset}px`"
            aria-hidden="true"
        ></div>
        <div
            x-show="loading"
            x-cloak
            x-transition:enter="transition-all duration-300 ease-out"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="absolute inset-x-0 z-20 flex justify-center pt-10 pointer-events-none"
            :style="`top: ${theadOffset}px`"
            role="status"
            aria-live="polite"
        >
            <svg
                class="h-9 w-9 animate-spin text-[#0f2340]"
                viewBox="0 0 36 36"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
            >
                <circle cx="18" cy="18" r="15" stroke="currentColor" stroke-width="2" class="opacity-15"></circle>
                <path
                    d="M33 18a15 15 0 0 0-15-15"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                ></path>
            </svg>
            <span class="sr-only">Chargement</span>
        </div>

        <table class="w-max min-w-full text-sm">
            <thead x-ref="tableHead" class="bg-gray-50 border-b border-gray-200">
                <tr x-ref="headerRow">
                    @foreach($selectedColumns as $columnKey)
                        @include('admin.users._table-column-header', [
                            'columnCatalog' => $columnCatalog,
                            'columnKey' => $columnKey,
                            'displaySortColumn' => $displaySortColumn,
                            'displaySortDirection' => $displaySortDirection,
                        ])
                    @endforeach
                    @if($sessionsManageable)
                        <th scope="col" class="px-4 py-2 text-center font-semibold text-gray-600 w-14">
                            <span class="sr-only">{{ __('messages.admin_sessions.col_online') }}</span>
                            <i class="fa-solid fa-signal text-gray-500 text-sm" title="{{ __('messages.admin_sessions.col_online') }}" aria-hidden="true"></i>
                        </th>
                    @endif
                    <th scope="col" class="px-4 py-2 text-center font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody
                class="divide-y divide-gray-100 bg-white transition-[opacity,filter] duration-300 ease-out"
                :class="loading ? 'opacity-[0.38] saturate-[0.82]' : 'opacity-100 saturate-100'"
            >
                @forelse($users as $user)
                    @php
                        $onlineCount = (int) ($sessionCounts[$user->id] ?? 0);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        @foreach($selectedColumns as $columnKey)
                            @include('admin.users._table-column-cell', [
                                'user' => $user,
                                'columnCatalog' => $columnCatalog,
                                'columnKey' => $columnKey,
                            ])
                        @endforeach
                        @if($sessionsManageable)
                            <td class="px-4 py-3 text-center">
                                @if($onlineCount > 0)
                                    @php
                                        $onlineLabel = trans_choice(
                                            'messages.admin_sessions.session_count_badge',
                                            $onlineCount,
                                            ['count' => $onlineCount]
                                        );
                                    @endphp
                                    <a href="{{ route('admin.sessions.index', ['user_id' => $user->id]) }}"
                                        class="relative inline-flex items-center justify-center w-9 h-9 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition-colors"
                                        title="{{ $onlineLabel }} — {{ __('messages.admin_sessions.view_sessions') }}"
                                        aria-label="{{ $onlineLabel }}">
                                        <i class="fa-solid fa-circle-check text-base" aria-hidden="true"></i>
                                        @if($onlineCount > 1)
                                            <span class="absolute -top-0.5 -right-0.5 min-w-[1rem] h-4 px-1 rounded-full bg-green-600 text-white text-[10px] font-bold leading-4 tabular-nums">
                                                {{ $onlineCount }}
                                            </span>
                                        @endif
                                    </a>
                                @else
                                    <span
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-full text-gray-300"
                                        title="{{ __('messages.admin_sessions.offline') }}"
                                        aria-label="{{ __('messages.admin_sessions.offline') }}">
                                        <i class="fa-solid fa-circle-minus text-base" aria-hidden="true"></i>
                                    </span>
                                @endif
                            </td>
                        @endif
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                @if($sessionsManageable && $onlineCount > 0 && $user->id !== auth()->id())
                                    <form action="{{ route('admin.sessions.destroy-user', $user) }}" method="POST"
                                        onsubmit="return confirm(@json(__('messages.admin_sessions.confirm_revoke_user')))">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                        <button type="submit"
                                            class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium"
                                            title="{{ __('messages.admin_sessions.disconnect_user') }}">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="px-2 py-1 rounded text-xs font-medium {{ $user->allowsLogin() ? 'bg-orange-100 text-orange-700 hover:bg-orange-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}"
                                            title="{{ $user->allowsLogin() ? 'Désactiver' : 'Activer' }}">
                                            <i class="fas {{ $user->allowsLogin() ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $tableColumnCount }}" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-users text-3xl mb-2 block"></i>
                            Aucun utilisateur trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div @click.capture="if ($event.target.closest('a[href], button')) startLoading()">
        {{ $users->links() }}
    </div>
    </div>
</div>
@endsection
