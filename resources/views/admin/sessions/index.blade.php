@extends('layouts.admin')

@section('title', __('messages.admin_sessions.title'))
@section('breadcrumb')
    <span class="text-gray-700 text-sm">{{ __('messages.admin_sessions.title') }}</span>
@endsection

@section('content')
@php
    $activeSessionService = app(\App\Services\Admin\ActiveSessionService::class);
    $deviceIcon = function (?string $userAgent) use ($activeSessionService): string {
        $label = $activeSessionService->deviceLabel($userAgent);
        if ($label === '—') {
            return 'fa-circle-question';
        }
        if ($label === 'Mobile') {
            return 'fa-mobile-screen';
        }
        if ($label === 'Windows') {
            return 'fa-brands fa-windows';
        }
        if ($label === 'macOS') {
            return 'fa-brands fa-apple';
        }
        if ($label === 'Linux') {
            return 'fa-brands fa-linux';
        }

        return 'fa-desktop';
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
                            <th class="px-4 py-3 text-left font-semibold text-gray-600" title="{{ __('messages.admin_sessions.location_hint') }}">{{ __('messages.admin_sessions.col_location') }}</th>
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
                                <td class="px-4 py-3 text-gray-600 font-mono text-xs">
                                    {{ filled($session['ip_address'] ?? null) ? $session['ip_address'] : '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">
                                    <span class="inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-gray-400 text-[0.7rem]" aria-hidden="true"></i>
                                        {{ $session['location_label'] ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600" title="{{ $session['user_agent'] ?? '' }}">
                                    <span class="inline-flex items-center gap-2">
                                        <i @class(['fa-solid text-gray-400', $deviceIcon($session['user_agent'] ?? null)]) aria-hidden="true"></i>
                                        <span>{{ $activeSessionService->deviceLabel($session['user_agent'] ?? null) }}</span>
                                    </span>
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
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500">
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

    <div class="pt-2 border-t border-gray-200 space-y-4">
        <div>
            <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">{{ __('messages.admin_sessions.login_stats_title') }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('messages.admin_sessions.login_stats_subtitle') }}</p>
        </div>

        @if(! $loginStatsAvailable)
            <div class="flex items-start gap-2 bg-amber-50 border border-amber-200 text-amber-900 rounded-lg px-4 py-3 text-sm">
                <i class="fas fa-database text-amber-500 mt-0.5"></i>
                <p>{{ __('messages.admin_sessions.login_stats_unavailable') }}</p>
            </div>
        @else
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('messages.admin_sessions.login_stats_today') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($loginStats['today'], 0, ',', ' ') }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('messages.admin_sessions.login_stats_7_days') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($loginStats['last_7_days'], 0, ',', ' ') }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('messages.admin_sessions.login_stats_30_days') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($loginStats['last_30_days'], 0, ',', ' ') }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('messages.admin_sessions.login_stats_unique_users') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($loginStats['unique_users_30_days'], 0, ',', ' ') }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 col-span-2 lg:col-span-1">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('messages.admin_sessions.login_stats_total') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($loginStats['total'], 0, ',', ' ') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="xl:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-700 text-sm">{{ __('messages.admin_sessions.login_stats_daily_title') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="text-left font-medium px-5 py-2">{{ __('messages.admin_sessions.login_stats_col_day') }}</th>
                                    <th class="text-right font-medium px-5 py-2">{{ __('messages.admin_sessions.login_stats_col_count') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($loginDailySeries as $day)
                                    <tr @class(['bg-indigo-50/40' => $day['date'] === now()->toDateString()])>
                                        <td class="px-5 py-2 text-gray-700">{{ \Illuminate\Support\Carbon::parse($day['date'])->translatedFormat('D d M Y') }}</td>
                                        <td class="px-5 py-2 text-right font-medium text-gray-800">{{ number_format($day['count'], 0, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-4">
                    @if($isManageable && count($activeDeviceBreakdown) > 0)
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                            <h3 class="font-semibold text-gray-700 text-sm mb-3">{{ __('messages.admin_sessions.login_stats_active_devices_title') }}</h3>
                            <ul class="space-y-2 text-sm">
                                @foreach($activeDeviceBreakdown as $label => $count)
                                    <li class="flex items-center justify-between gap-2">
                                        <span class="text-gray-700">{{ $label }}</span>
                                        <span class="font-semibold text-gray-900 tabular-nums">{{ $count }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(count($loginDeviceBreakdown) > 0)
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                            <h3 class="font-semibold text-gray-700 text-sm mb-3">{{ __('messages.admin_sessions.login_stats_devices_title') }}</h3>
                            <ul class="space-y-2 text-sm">
                                @foreach($loginDeviceBreakdown as $label => $count)
                                    <li class="flex items-center justify-between gap-2">
                                        <span class="text-gray-700">{{ $label }}</span>
                                        <span class="font-semibold text-gray-900 tabular-nums">{{ $count }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-700 text-sm">{{ __('messages.admin_sessions.login_stats_recent_title') }}</h3>
                </div>
                @if($recentLogins->isEmpty())
                    <p class="px-5 py-8 text-sm text-gray-500 text-center">{{ __('messages.admin_sessions.login_stats_no_data') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="text-left font-medium px-5 py-2">{{ __('messages.admin_sessions.login_stats_col_user') }}</th>
                                    <th class="text-left font-medium px-5 py-2">{{ __('messages.admin_sessions.col_ip') }}</th>
                                    <th class="text-left font-medium px-5 py-2">{{ __('messages.admin_sessions.col_location') }}</th>
                                    <th class="text-left font-medium px-5 py-2">{{ __('messages.admin_sessions.col_device') }}</th>
                                    <th class="text-right font-medium px-5 py-2">{{ __('messages.admin_sessions.login_stats_col_when') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($recentLogins as $login)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-2">
                                            @if($login->user)
                                                <p class="font-medium text-gray-800">{{ $login->user->name }}</p>
                                                <p class="text-xs text-gray-400">{{ $login->user->email }}</p>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-2 font-mono text-xs text-gray-600">{{ filled($login->ip_address) ? $login->ip_address : '—' }}</td>
                                        <td class="px-5 py-2 text-gray-600 text-xs">{{ app(\App\Services\Admin\UserLoginStatisticsService::class)->locationLabelForLoginEvent($login) }}</td>
                                        <td class="px-5 py-2 text-gray-600">{{ $activeSessionService->deviceLabel($login->user_agent) }}</td>
                                        <td class="px-5 py-2 text-right text-gray-600 whitespace-nowrap">
                                            {{ $login->logged_in_at->translatedFormat('d/m/Y H:i') }}
                                            <span class="block text-xs text-gray-400">{{ $login->logged_in_at->diffForHumans() }}</span>
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
</div>
@endsection
