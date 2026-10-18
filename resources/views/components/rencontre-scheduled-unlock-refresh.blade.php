@props([
    'opensAt' => null,
])

@php
    $opensAtCarbon = $opensAt instanceof \Carbon\Carbon ? $opensAt : null;
    $shouldSchedule = $opensAtCarbon && $opensAtCarbon->isFuture();
@endphp

@if($shouldSchedule)
    <span
        class="hidden"
        data-unlock-reload-at="{{ $opensAtCarbon->toIso8601String() }}"
        aria-hidden="true"></span>
@endif
