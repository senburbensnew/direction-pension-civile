@php
    $now = now()->timezone('America/Port-au-Prince');
    $weekdays = __('messages.weekdays_short');
    $months = __('messages.months_short');
    $initialDatetime = $weekdays[$now->dayOfWeek]
        .' '.$now->format('d').' '
        .$months[$now->month - 1]
        .' '.$now->format('Y, H:i:s');
@endphp
<div class="container mx-auto relative overflow-hidden bg-[#173052] bg-motif-dots text-white text-sm
    @if($borderType === 'top') border-t border-white/20 @endif
    @if($borderType === 'bottom') border-b border-white/20 @endif
">
    <div class="relative z-10 px-4 py-2 md:py-0 md:h-10 flex flex-col md:flex-row items-center justify-center md:justify-between gap-1 md:gap-4">
            <!-- Contact Info + Hours -->
            <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-4 text-center md:text-left">
                <!-- Hours -->
                <div class="flex items-center justify-center md:justify-start gap-1">
                    <i class="fas fa-clock text-xs"></i>
                    <span>{{ __('messages.opening_hours') }}</span>
                </div>

                <span class="hidden md:inline opacity-50">|</span>

                <!-- Email -->
                <a href="mailto:dpc.info@mef.gouv.ht" class="flex items-center justify-center md:justify-start gap-1 hover:text-orange-400 transition-colors">
                    <i class="fas fa-envelope text-xs"></i>
                    <span>dpc.info@mef.gouv.ht</span>
                </a>

                <span class="hidden md:inline opacity-50">|</span>

                <!-- Phone -->
                <a href="tel:+50929921007" class="flex items-center justify-center md:justify-start gap-1 hover:text-orange-400 transition-colors">
                    <i class="fas fa-phone text-xs"></i>
                    <span>{{ __('messages.call_on', ['phone' => '+509 29 92 1007']) }}</span>
                </a>
            </div>

            <div class="flex items-center gap-1.5 shrink-0 tabular-nums whitespace-nowrap text-white/90"
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
                <i class="fas fa-calendar-alt text-xs"></i>
                <span x-text="now">{{ $initialDatetime }}</span>
            </div>
    </div>
</div>
