<style>
    .tooltip-container {
        position: relative;
        display: inline-block;
        cursor: help;
    }

    .tooltip-text {
        visibility: hidden;
        width: 120px;
        background-color: black;
        color: white;
        text-align: center;
        border-radius: 5px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 100%;
        left: 50%;
        margin-left: -60px;
        /* Half of the width for centering */
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .tooltip-container:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    .tooltip-text::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: black transparent transparent transparent;
    }
</style>

<span class="tooltip-container" {{ $attributes->merge(['class' => 'cursor-help']) }}>
    {{ $slot }}
    <span class="tooltip-text">{{ $text }}</span>
</span>
