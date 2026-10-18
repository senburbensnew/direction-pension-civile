@extends('layouts.admin')

@section('title', __('messages.admin_sessions.title'))
@section('breadcrumb')
    <span class="text-gray-700 text-sm">{{ __('messages.admin_sessions.title') }}</span>
@endsection

@section('content')
@php
    $parseUserAgent = function (?string $ua): string {
        if ($ua === null || $ua === '') {
            return '—';
        }
        if (str_contains($ua, 'Mobile')) {
            return 'Mobile';
        }
        if (str_contains($ua, 'Windows')) {
            return 'Windows';
        }
        if (str_contains($ua, 'Mac OS') || str_contains($ua, 'Macintosh')) {
            return 'macOS';
        }
        if (str_contains($ua, 'Linux')) {
            return 'Linux';
        }

        return \Illuminate\Support\Str::limit($ua, 40);
    };
@endphp

<div class="space-y-4">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">{{ __('messages.admin_sessions.title') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('messages.admin_sessions.subtitle') }}</p>
        </div>
    </div>

    @if(! $isManageable)
        <div class="flex items-start gap-2 bg-amber-50 border border-amber-200 text-amber-900 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
            <p>{{ __('messages.admin_sessions.unsupported_driver', ['driver' => $driver]) }}</p>
        </div>
    @else
        @if($driver === 'file')
            <div class="flex items-start gap-2 bg-blue-50 border border-blue-200 text-blue-900 rounded-lg px-4 py-3 text-sm">
                <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                <p>{{ __('messages.admin_sessions.file_driver_hint') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('messages.admin_sessions.stats_sessions') }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['session_count'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('messages.admin_sessions.stats_users') }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['user_count'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('messages.admin_sessions.stats_lifetime') }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $lifetimeMinutes }} min</p>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('messages.admin_sessions.filter_search') }}</label>
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="{{ __('messages.admin_sessions.filter_search_placeholder') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('messages.admin_sessions.filter_user') }}</label>
                <select name="user_id"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-[12rem] focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('messages.admin_sessions.filter_user_all') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((int) request('user_id') === $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 pb-2">
                <input type="checkbox" name="include_guests" value="1" @checked(request()->boolean('include_guests'))
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                {{ __('messages.admin_sessions.include_guests') }}
            </label>
            <button type="submit"
                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg">
                <i class="fas fa-filter mr-1"></i> {{ __('messages.admin_sessions.apply_filters') }}
            </button>
            @if(request()->hasAny(['q', 'user_id', 'include_guests']))
                <a href="{{ route('admin.sessions.index') }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm rounded-lg">
                    <i class="fas fa-times mr-1"></i> {{ __('messages.admin_sessions.clear_filters') }}
                </a>
            @endif
        </form>

        @if(request()->filled('user_id'))
            @php
                $filteredUser = $users->firstWhere('id', (int) request('user_id'));
            @endphp
            @if($filteredUser && $stats['session_count'] > 0)
                <div class="flex flex-wrap items-center justify-between gap-3 bg-white border border-gray-200 rounded-lg px-4 py-3">
                    <p class="text-sm text-gray-700">
                        {{ __('messages.admin_sessions.filtered_user_hint', ['name' => $filteredUser->name]) }}
                    </p>
                    <form method="POST"
                        action="{{ route('admin.sessions.destroy-user', $filteredUser) }}"
                        onsubmit="return confirm(@json(__('messages.admin_sessions.confirm_revoke_user')))">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-amber-600 hover:bg-amber-700 text-white">
                            <i class="fas fa-user-slash"></i>
                            {{ __('messages.admin_sessions.disconnect_user') }}
                        </button>
                    </form>
                </div>
            @endif
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('messages.admin_sessions.col_user') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('messages.admin_sessions.col_ip') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('messages.admin_sessions.col_device') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('messages.admin_sessions.col_last_activity') }}</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">{{ __('messages.admin_sessions.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($sessions as $session)
                            @php
                                $isCurrent = $session['id'] === $currentSessionId;
                            @endphp
                            <tr class="hover:bg-gray-50 {{ $isCurrent ? 'bg-indigo-50/40' : '' }}">
                                <td class="px-4 py-3">
                                    @if($session['user'])
                                        <div class="font-medium text-gray-800">{{ $session['user']->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $session['user']->email }}</div>
                                    @else
                                        <span class="text-gray-500 italic">{{ __('messages.admin_sessions.guest') }}</span>
                                    @endif
                                    @if($isCurrent)
                                        <span class="inline-flex mt-1 items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[0.65rem] font-semibold text-indigo-700">
                                            {{ __('messages.admin_sessions.current_session') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $session['ip_address'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600" title="{{ $session['user_agent'] }}">
                                    {{ $parseUserAgent($session['user_agent']) }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ \Carbon\Carbon::createFromTimestamp($session['last_activity'])->diffForHumans() }}
                                    <div class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::createFromTimestamp($session['last_activity'])->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        @if(! $isCurrent)
                                            <form method="POST"
                                                action="{{ route('admin.sessions.destroy', $session['id']) }}"
                                                onsubmit="return confirm(@json(__('messages.admin_sessions.confirm_revoke_one')))">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100">
                                                    <i class="fas fa-power-off"></i>
                                                    {{ __('messages.admin_sessions.terminate_session') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                    <i class="fas fa-user-clock text-2xl text-gray-300 mb-2"></i>
                                    <p>{{ __('messages.admin_sessions.empty') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
