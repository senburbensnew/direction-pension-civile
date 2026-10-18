@php
    $manageable = $manageable ?? true;
    $sessions = $sessions ?? collect();
    $currentSessionId = session()->getId();
    $activeSessionService = app(\App\Services\Admin\ActiveSessionService::class);
    $loginStatistics = app(\App\Services\Admin\UserLoginStatisticsService::class);
@endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3 bg-gray-50">
        <div class="flex items-center gap-2">
            <i class="fas fa-plug text-indigo-500 text-sm w-4 text-center"></i>
            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">
                {{ __('messages.admin_sessions.user_panel_title') }}
            </span>
            @if($sessions->count() > 0)
                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[0.65rem] font-semibold text-green-800">
                    {{ trans_choice('messages.admin_sessions.session_count_badge', $sessions->count(), ['count' => $sessions->count()]) }}
                </span>
            @endif
        </div>
        @if($manageable && $sessions->count() > 0 && ! $user->is(auth()->user()))
            <form method="POST"
                action="{{ route('admin.sessions.destroy-user', $user) }}"
                onsubmit="return confirm(@json(__('messages.admin_sessions.confirm_revoke_user')))">
                @csrf
                @method('DELETE')
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-red-600 hover:bg-red-700 text-white">
                    <i class="fas fa-sign-out-alt"></i>
                    {{ __('messages.admin_sessions.disconnect_user') }}
                </button>
            </form>
        @endif
    </div>

    <div class="p-5">
        @if(! $manageable)
            <p class="text-sm text-gray-500">{{ __('messages.admin_sessions.user_panel_unavailable') }}</p>
        @elseif($sessions->isEmpty())
            <p class="text-sm text-gray-500">{{ __('messages.admin_sessions.user_panel_empty') }}</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach($sessions as $session)
                    @php($isCurrent = $session['id'] === $currentSessionId)
                    <li class="py-3 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm text-gray-800">
                                {{ $activeSessionService->deviceLabel($session['user_agent'] ?? null) }}
                                <span class="text-gray-400">·</span>
                                <span class="font-mono text-xs text-gray-500">{{ filled($session['ip_address'] ?? null) ? $session['ip_address'] : '—' }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <i class="fa-solid fa-location-dot text-gray-400 mr-1" aria-hidden="true"></i>
                                {{ $session['location_label'] ?? $loginStatistics->locationLabelForIp($session['ip_address'] ?? null) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ __('messages.admin_sessions.last_seen') }}
                                {{ \Carbon\Carbon::createFromTimestamp($session['last_activity'])->diffForHumans() }}
                            </p>
                            @if($isCurrent)
                                <span class="inline-flex mt-1 items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[0.65rem] font-semibold text-indigo-700">
                                    {{ __('messages.admin_sessions.current_session') }}
                                </span>
                            @endif
                        </div>
                        @if(! $isCurrent)
                            <form method="POST"
                                action="{{ route('admin.sessions.destroy', $session['id']) }}"
                                onsubmit="return confirm(@json(__('messages.admin_sessions.confirm_revoke_one')))">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                <button type="submit"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 whitespace-nowrap">
                                    <i class="fas fa-power-off"></i>
                                    {{ __('messages.admin_sessions.terminate_session') }}
                                </button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
