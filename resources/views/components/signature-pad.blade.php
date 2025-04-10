@props(['name', 'id' => null, 'disablePad' => false])

<div class="signature-container">
    @error($name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    <input type="hidden" id="{{ $id ?? $name }}" name="{{ $name }}" />

    <canvas id="{{ $id ?? $name }}-canvas"
        class="@error($name) border-red-500 @enderror @if ($disablePad) pointer-events-none @endif"
        style="touch-action: none"></canvas>

    <div class="button-container">
        <div class="left-buttons">
            <button type="button" id="clear-{{ $id ?? $name }}" class="clear-btn"
                @if ($disablePad) disabled @endif aria-label="Supprimer">
                effacer
                <svg viewBox="0 0 24 24" width="20" height="20">
                    <path fill="currentColor"
                        d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
                </svg>
            </button>
            <button type="button" id="confirm-{{ $id ?? $name }}" class="confirm-btn"
                @if ($disablePad) disabled @endif>
                valider &check;
            </button>
        </div>
        <div class="color-options">
            <div class="color-option active" data-color="#070707" title="Black Ink">
                <div class="color-circle"></div>
            </div>
            <div class="color-option" data-color="#1a0080" title="Blue Pen Ink">
                <div class="color-circle"></div>
            </div>
        </div>
    </div>

    @if ($disablePad)
        <div class="w-full h-full bg-gray-100/50 absolute inset-0 z-10 flex items-center justify-center">
            <span class="bg-white p-3 rounded-lg border shadow-sm text-gray-600">
                Signature désactivée
            </span>
        </div>
    @endif
</div>

<style>
    .signature-container {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 15px;
        width: 100%;
    }

    .signature-container canvas {
        width: 100%;
        height: 200px;
        border: 2px dotted #d3d3d3;
        background-color: transparent;
        background-image: linear-gradient(45deg, #f0f0f0 25%, transparent 25%),
            linear-gradient(-45deg, #f0f0f0 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #f0f0f0 75%),
            linear-gradient(-45deg, transparent 75%, #f0f0f0 75%);
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>') 0 16, auto;
    }

    .button-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }

    .left-buttons {
        display: flex;
        gap: 10px;
    }

    .color-options {
        display: flex;
        gap: 10px;
    }

    .color-option {
        cursor: pointer;
        padding: 3px;
        border: 2px solid transparent;
        border-radius: 50%;
        transition: border-color 0.2s;
    }

    .color-option.active {
        border-color: #666;
    }

    .color-circle {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .color-option[data-color="#070707"] .color-circle {
        background-color: #070707;
    }

    .color-option[data-color="#1a0080"] .color-circle {
        background-color: #1a0080;
    }

    .clear-btn,
    .confirm-btn {
        padding: 8px 12px;
        border-radius: 5px;
        // border: 1px solid #ddd;
        // background: white;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .clear-btn {
        color: #e74c3c;
    }

    .confirm-btn {
        color: #2ecc71;
    }

    .clear-btn:hover:not(:disabled),
    .confirm-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        // box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .clear-btn:disabled,
    .confirm-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    @endpush
@endonce

<script>
    (function() {
        const initSignaturePad = () => {
            const canvas = document.getElementById('{{ $id ?? $name }}-canvas');
            const input = document.getElementById('{{ $id ?? $name }}');
            if (!canvas || !input) return;

            // Don't initialize if pad is disabled
            @if (!$disablePad)
                // Initialize Signature Pad
                const signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 0)',
                    penColor: '#070707'
                });

                // High DPI support
                const updateCanvasSize = () => {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);
                    signaturePad.clear();
                };
                updateCanvasSize();

                // Color selection
                const colorOptions = document.querySelectorAll(
                    `#{{ $id ?? $name }}-canvas ~ .button-container .color-option`);
                colorOptions.forEach(option => {
                    option.addEventListener('click', () => {
                        colorOptions.forEach(opt => opt.classList.remove('active'));
                        option.classList.add('active');
                        signaturePad.penColor = option.dataset.color;
                    });
                });

                // Clear button
                document.getElementById('clear-{{ $id ?? $name }}').addEventListener('click', () => {
                    signaturePad.clear();
                    input.value = '';
                    canvas.style.borderColor = '#d3d3d3';
                });

                // Confirm button
                document.getElementById('confirm-{{ $id ?? $name }}').addEventListener('click', () => {
                    if (signaturePad.isEmpty()) {
                        alert('Veuillez fournir une signature d\'abord !');
                        return;
                    }
                    input.value = signaturePad.toDataURL('image/png');
                    canvas.style.borderColor = '#2ecc71';
                });

                // Window resize handler
                let resizeTimer;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(() => {
                        const data = signaturePad.toData();
                        updateCanvasSize();
                        signaturePad.fromData(data);
                    }, 200);
                });
            @endif
        };

        if (document.readyState === 'complete') {
            initSignaturePad();
        } else {
            document.addEventListener('DOMContentLoaded', initSignaturePad);
        }
    })();
</script>
