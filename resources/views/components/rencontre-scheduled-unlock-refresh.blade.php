@props([
    'opensAt' => null,
])

@php
    $opensAtCarbon = $opensAt instanceof \Carbon\Carbon ? $opensAt : null;
    $shouldSchedule = $opensAtCarbon && $opensAtCarbon->isFuture();
@endphp

@if($shouldSchedule)
    <script>
        window.__dpcScheduleUnlockReload = window.__dpcScheduleUnlockReload || function () {};
    </script>
    <span
        class="hidden"
        data-unlock-reload-at="{{ $opensAtCarbon->toIso8601String() }}"
        aria-hidden="true"></span>
@endif
