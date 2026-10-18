@php
    $now = now()->timezone('America/Port-au-Prince');
    $weekdays = __('messages.weekdays_short');
    $months = __('messages.months_short');
    $initialDatetime = $weekdays[$now->dayOfWeek]
        .' '.$now->format('d').' '
        .$months[$now->month - 1]
        .' '.$now->format('Y, H:i:s');
    $servicePhones = config('contact.service_phones', []);
    $opensUp = $borderType === 'top';
@endphp
<div class="relative bg-[#173052] bg-motif-dots text-white text-sm sm:text-base @if($borderType === 'top') border-t border-white/20 @endif @if($borderType === 'bottom') border-b border-white/20 @endif"
     x-data="{ open: false }"
     :class="open && 'z-[1100]'"
     @keydown.escape.window="open = false">
    <div class="site-gutter relative z-10 flex flex-col items-center gap-2 py-2.5 text-center xl:flex-row xl:items-center xl:justify-between xl:gap-4 xl:text-left">
        <div class="flex w-full max-w-full flex-col items-center gap-1.5 lg:flex-row lg:flex-wrap lg:justify-center lg:gap-x-4 lg:gap-y-1.5 xl:w-auto xl:flex-nowrap xl:justify-start">
            <div class="inline-flex max-w-[min(100%,20rem)] items-start justify-center gap-1.5 leading-snug sm:max-w-none sm:items-center lg:whitespace-nowrap">
                <i class="fas fa-clock mt-0.5 text-xs sm:mt-0" aria-hidden="true"></i>
                <span>{{ __('messages.opening_hours') }}</span>
            </div>

            <span class="hidden 2xl:inline opacity-50" aria-hidden="true">|</span>

            <a href="mailto:dpc.info@mef.gouv.ht"
               class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap hover:text-orange-400 transition-colors">
                <i class="fas fa-envelope text-xs" aria-hidden="true"></i>
                <span>dpc.info@mef.gouv.ht</span>
            </a>

            <span class="hidden 2xl:inline opacity-50" aria-hidden="true">|</span>

            <div class="relative flex justify-center xl:justify-start" @click.outside="open = false">
                <button type="button"
                        class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap hover:text-orange-400 transition-colors"
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                        aria-haspopup="true">
                    <i class="fas fa-phone text-xs" aria-hidden="true"></i>
                    <span>{{ __('messages.service_phones') }}</span>
                    <i class="fas fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'" aria-hidden="true"></i>
                </button>
                <div x-show="open"
                     x-cloak
                     x-transition.origin.top
                     class="absolute left-1/2 z-[1100] w-80 max-w-[calc(100vw-1.5rem)] -translate-x-1/2 border border-gray-200 bg-white text-left text-gray-800 shadow-lg xl:left-0 xl:translate-x-0 {{ $opensUp ? 'bottom-full mb-2' : 'top-full mt-2' }}">
                    <p class="border-b border-gray-200 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-[#173052]">
                        {{ __('messages.service_phones_intro') }}
                    </p>
                    <ul>
                        @foreach($servicePhones as $line)
                            <li>
                                <a href="tel:{{ $line['phone'] }}"
                                   class="flex flex-col gap-0.5 px-3 py-2 hover:bg-gray-50">
                                    <span class="text-xs text-gray-600">{{ $line['label'] }}</span>
                                    <span class="font-semibold text-[#173052]">{{ $line['display'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="inline-flex shrink-0 items-center justify-center gap-1.5 tabular-nums whitespace-nowrap text-white/90 xl:justify-end"
             x-data="{
                now: {{ json_encode($initialDatetime) }},
                weekdays: {{ json_encode($weekdays) }},
                months: {{ json_encode($months) }},
                tick() {
                    const parts = Object.fromEntries(
                        new Intl.DateTimeFormat('en-US', {
                            timeZone: 'America/Port-au-Prince',
                            weekday: 'short',
                            year: 'numeric',
                            month: 'numeric',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false,
                            hourCycle: 'h23'
                        }).formatToParts(new Date()).map(p => [p.type, p.value])
                    );
                    const weekdayIndex = { Sun: 0, Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6 }[parts.weekday] ?? 0;
                    const monthIndex = Math.max(0, parseInt(parts.month, 10) - 1);
                    this.now = this.weekdays[weekdayIndex]
                        + ' ' + parts.day
                        + ' ' + this.months[monthIndex]
                        + ' ' + parts.year
                        + ', ' + parts.hour + ':' + parts.minute + ':' + parts.second;
                }
             }"
             x-init="tick(); setInterval(() => tick(), 1000)">
            <i class="fas fa-calendar-alt text-xs" aria-hidden="true"></i>
            <span x-text="now">{{ $initialDatetime }}</span>
        </div>
    </div>
</div>
