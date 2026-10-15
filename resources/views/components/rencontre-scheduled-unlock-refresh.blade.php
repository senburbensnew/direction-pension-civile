@props([
    /** @var \Carbon\Carbon|null Instant when server-side actions unlock (clôture, formalité, etc.) */
    'opensAt' => null,
])

@php
    $opensAtCarbon = $opensAt instanceof \Carbon\Carbon ? $opensAt : null;
    $shouldSchedule = $opensAtCarbon && $opensAtCarbon->isFuture();
@endphp

@if($shouldSchedule)
    @push('scripts')
        @once
            <script>
                window.__dpcUnlockReloadSeen = window.__dpcUnlockReloadSeen || new Set();

                window.__dpcScheduleUnlockReload = function (isoString) {
                    if (!isoString || window.__dpcUnlockReloadSeen.has(isoString)) {
                        return;
                    }
                    window.__dpcUnlockReloadSeen.add(isoString);

                    const targetMs = new Date(isoString).getTime();
                    if (Number.isNaN(targetMs)) {
                        return;
                    }

                    const MAX_DELAY = 2147483647;
                    const GRACE_MS = 500;

                    const reloadIfDue = function () {
                        if (Date.now() >= targetMs) {
                            window.location.reload();
                            return true;
                        }
                        return false;
                    };

                    const schedule = function () {
                        if (reloadIfDue()) {
                            return;
                        }

                        let delay = targetMs - Date.now() + GRACE_MS;
                        if (delay <= 0) {
                            window.location.reload();
                            return;
                        }
                        if (delay > MAX_DELAY) {
                            window.setTimeout(schedule, MAX_DELAY);
                            return;
                        }
                        window.setTimeout(function () {
                            window.location.reload();
                        }, delay);
                    };

                    document.addEventListener('visibilitychange', function onVisible() {
                        if (!document.hidden && reloadIfDue()) {
                            document.removeEventListener('visibilitychange', onVisible);
                        }
                    });

                    schedule();
                };
            </script>
        @endonce
        <script>
            window.__dpcScheduleUnlockReload(@json($opensAtCarbon->toIso8601String()));
        </script>
    @endpush
@endif
