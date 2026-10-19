@php
    $timeline = $timeline ?? collect();
    $activeSessionService = app(\App\Services\Admin\ActiveSessionService::class);
@endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2 bg-gray-50">
        <div class="flex items-center gap-2">
            <i class="fas fa-clock-rotate-left text-indigo-500 text-sm w-4 text-center"></i>
            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">
                {{ __('messages.admin_sessions.connection_trace_title') }}
            </span>
        </div>
        <a href="{{ route('admin.sessions.index', ['user_id' => $user->id]) }}"
            class="text-xs font-medium text-[#173052] hover:text-orange-600 transition-colors">
            {{ __('messages.admin_sessions.connection_trace_all_sessions') }}
        </a>
    </div>

    @if($timeline->isEmpty())
        <p class="px-5 py-6 text-sm text-gray-500">{{ __('messages.admin_sessions.connection_trace_empty') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="text-left font-medium px-5 py-2">{{ __('messages.admin_sessions.connection_trace_col_event') }}</th>
                        <th class="text-left font-medium px-5 py-2">{{ __('messages.admin_sessions.col_ip') }}</th>
                        <th class="text-left font-medium px-5 py-2">{{ __('messages.admin_sessions.col_location') }}</th>
                        <th class="text-left font-medium px-5 py-2">{{ __('messages.admin_sessions.col_device') }}</th>
                        <th class="text-right font-medium px-5 py-2">{{ __('messages.admin_sessions.login_stats_col_when') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($timeline as $entry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-2">
                                @if($entry['type'] === 'login')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-800">
                                        <i class="fas fa-right-to-bracket text-[0.65rem]" aria-hidden="true"></i>
                                        {{ $entry['detail_label'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-800">
                                        <i class="fas fa-right-from-bracket text-[0.65rem]" aria-hidden="true"></i>
                                        {{ $entry['detail_label'] }}
                                    </span>
                                    @if(filled($entry['actor_name'] ?? null))
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{ __('messages.admin_sessions.connection_trace_by', ['name' => $entry['actor_name']]) }}
                                        </p>
                                    @endif
                                @endif
                            </td>
                            <td class="px-5 py-2 font-mono text-xs text-gray-600">{{ filled($entry['ip_address']) ? $entry['ip_address'] : '—' }}</td>
                            <td class="px-5 py-2 text-gray-600 text-xs">{{ $entry['location_label'] }}</td>
                            <td class="px-5 py-2 text-gray-600">{{ $activeSessionService->deviceLabel($entry['user_agent'] ?? null) }}</td>
                            <td class="px-5 py-2 text-right text-gray-600 whitespace-nowrap">
                                {{ $entry['at']->translatedFormat('d/m/Y H:i') }}
                                <span class="block text-xs text-gray-400">{{ $entry['at']->diffForHumans() }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
