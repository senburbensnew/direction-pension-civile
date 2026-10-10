@props([
    'token',
    /** Poll while the visio window has this status; reload when it changes. */
    'while' => 'en_attente',
])

@if(filled($token) && filled($while))
    @push('scripts')
        @once
            <script>
                window.__dpcVisioPolls = window.__dpcVisioPolls || [];
                window.__dpcVisioPollSeen = window.__dpcVisioPollSeen || new Set();

                window.__dpcScheduleVisioPoll = function (entry, delayMs) {
                    window.setTimeout(async function tick() {
                        if (document.hidden) {
                            window.__dpcScheduleVisioPoll(entry, delayMs);
                            return;
                        }

                        let nextDelay = delayMs;

                        try {
                            const response = await fetch(entry.url, {
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            });

                            if (!response.ok) {
                                window.__dpcScheduleVisioPoll(entry, nextDelay);
                                return;
                            }

                            const data = await response.json();

                            if (data.status !== entry.while) {
                                window.location.reload();
                                return;
                            }

                            if (data.opens_at) {
                                const msUntilOpen = new Date(data.opens_at).getTime() - Date.now();
                                if (msUntilOpen > 0 && msUntilOpen <= 120000) {
                                    nextDelay = 5000;
                                } else if (msUntilOpen > 120000) {
                                    nextDelay = 15000;
                                }
                            }
                        } catch (e) {
                            /* ignore network errors; retry */
                        }

                        window.__dpcScheduleVisioPoll(entry, nextDelay);
                    }, delayMs);
                };

                window.__dpcRegisterVisioPoll = function (entry) {
                    if (!entry?.url || window.__dpcVisioPollSeen.has(entry.url)) {
                        return;
                    }
                    window.__dpcVisioPollSeen.add(entry.url);
                    window.__dpcScheduleVisioPoll(entry, 10000);
                };
            </script>
        @endonce
        <script>
            window.__dpcVisioPolls = window.__dpcVisioPolls || [];
            window.__dpcRegisterVisioPoll({
                url: @json(route('demandes.rencontre.visio.status', $token)),
                while: @json($while),
            });
        </script>
    @endpush
@endif
