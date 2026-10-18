@props(['name', 'id' => null, 'disablePad' => false])

@php($fieldKey = $id ?? $name)

<div
    data-signature-pad="{{ $fieldKey }}"
    data-signature-disabled="{{ $disablePad ? '1' : '0' }}"
    class="relative flex w-full flex-col gap-4">
    @if (! $disablePad)
        <input type="hidden" id="{{ $fieldKey }}" name="{{ $name }}" data-signature-input />
    @endif
    <canvas
        id="{{ $fieldKey }}-canvas"
        data-signature-canvas
        @class([
            'h-[200px] w-full touch-none border-2 border-dotted border-gray-300 bg-transparent',
            'border-red-500' => $errors->has($name),
            'pointer-events-none' => $disablePad,
        ])></canvas>

    <div class="flex items-center justify-between gap-4">
        <div class="flex gap-2.5">
            <button
                type="button"
                id="clear-{{ $fieldKey }}"
                @disabled($disablePad)
                class="flex items-center gap-1.5 rounded-md px-3 py-2 text-[#e74c3c] transition enabled:hover:-translate-y-px disabled:cursor-not-allowed disabled:opacity-50"
                aria-label="Supprimer">
                effacer
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                    <path fill="currentColor"
                        d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
                </svg>
            </button>
            <button
                type="button"
                id="confirm-{{ $fieldKey }}"
                @disabled($disablePad)
                class="flex items-center gap-1.5 rounded-md px-3 py-2 text-[#2ecc71] transition enabled:hover:-translate-y-px disabled:cursor-not-allowed disabled:opacity-50">
                valider &check;
            </button>
        </div>
        <div class="flex gap-2.5">
            <div
                class="color-option cursor-pointer rounded-full border-2 border-gray-600 p-0.5 transition-colors"
                data-color="#070707"
                title="Black Ink">
                <div class="h-7 w-7 rounded-full border border-black/10 bg-[#070707]"></div>
            </div>
            <div
                class="color-option cursor-pointer rounded-full border-2 border-transparent p-0.5 transition-colors"
                data-color="#1a0080"
                title="Blue Pen Ink">
                <div class="h-7 w-7 rounded-full border border-black/10 bg-[#1a0080]"></div>
            </div>
        </div>
    </div>
    @if ($disablePad)
        <div class="absolute inset-0 z-10 flex h-full w-full items-center justify-center bg-gray-100/50">
            <span class="rounded-lg border bg-white p-3 text-sm text-gray-600 shadow-sm">
                Signature désactivée
            </span>
        </div>
    @endif
    @error($name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
