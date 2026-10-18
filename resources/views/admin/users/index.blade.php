@extends('layouts.admin')

@section('title', 'Utilisateurs')
@section('breadcrumb')
    <span class="text-gray-700 text-sm">Utilisateurs</span>
@endsection

@section('content')
<div class="space-y-4">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl font-bold text-gray-800">Utilisateurs</h1>
        <a href="{{ route('admin.users.create') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-user-plus"></i> Créer un utilisateur
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ request('q') }}"
            placeholder="Rechercher par nom ou email…"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">

        <select name="role" onchange="this.form.submit()"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Tous les rôles</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                    {{ ucfirst($role->name) }}
                </option>
            @endforeach
        </select>

        <button type="submit"
            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg">
            <i class="fas fa-search mr-1"></i> Filtrer
        </button>

        @if(request('q') || request('role'))
            <a href="{{ route('admin.users.index') }}"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm rounded-lg">
                <i class="fas fa-times mr-1"></i> Effacer
            </a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Utilisateur</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Type</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Service</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Rôles</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Statut</th>
                    @if($sessionsManageable)
                        <th class="px-4 py-3 text-center font-semibold text-gray-600 w-14">
                            <span class="sr-only">{{ __('messages.admin_sessions.col_online') }}</span>
                            <i class="fa-solid fa-signal text-gray-500 text-sm" title="{{ __('messages.admin_sessions.col_online') }}" aria-hidden="true"></i>
                        </th>
                    @endif
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                    @php
                        $onlineCount = (int) ($sessionCounts[$user->id] ?? 0);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($user->profile_photo)
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                        class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-indigo-600 text-xs font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $user->primaryCategoryCode() ? ucfirst(str_replace('_', ' ', $user->primaryCategoryCode())) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $user->service->nom ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $role->name === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-gray-400 text-xs">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $status = $user->resolvedAccountStatus();
                            @endphp
                            <span @class([
                                'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
                                'bg-green-100 text-green-700' => $status->isActif(),
                                'bg-amber-100 text-amber-800' => $status->isProvisionnel(),
                                'bg-red-100 text-red-700' => $status === \App\Enums\AccountStatusEnum::BLOQUE,
                                'bg-gray-100 text-gray-500' => $status === \App\Enums\AccountStatusEnum::DESACTIVE,
                            ])>
                                {{ $status->label() }}
                            </span>
                        </td>
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
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                        onsubmit="return confirm('Supprimer {{ addslashes($user->name) }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $sessionsManageable ? 7 : 6 }}" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-users text-3xl mb-2 block"></i>
                            Aucun utilisateur trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $users->withQueryString()->links() }}</div>
</div>
@endsection
