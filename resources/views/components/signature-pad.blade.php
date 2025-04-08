{{-- <div class="signature-container">
    @error('signature')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    <input type="hidden" type="file" accept="image/*" id="signature-input" name="signature" />

    <canvas id="signature-pad" class="@error('signature') border-red-500 @enderror"></canvas>
    <div class="button-container">
        <div class="left-buttons">
            <span id="clear" aria-label="Supprimer">
                effacer
                <svg viewBox="0 0 24 24" width="20" height="20">
                    <path fill="currentColor"
                        d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
                </svg>
            </span>
            <span id="confirm">valider &check;</span>
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
</div>

<style>
    .signature-container {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        width: 100%;
    }

    .signature-container canvas {
        width: 100%;
        height: 150px;
        border: 2px dotted #d3d3d3;
        background-color: transparent;
        cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>') 0 16, auto;
        touch-action: none;
    }

    .button-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        gap: 15px;
    }

    .left-buttons {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .color-options {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .color-option {
        cursor: pointer;
        padding: 3px;
        border: 2px solid transparent;
        border-radius: 50%;
        transition: all 0.2s ease;
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

    #clear {
        color: #e74c3c;
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 8px 12px;
        border-radius: 5px;
        /* background-color: #ffe6e6; */
        border: none;
        transition: all 0.3s ease;
    }

    #confirm {
        color: #2ecc71;
        padding: 8px 12px;
        border-radius: 5px;
        /* background-color: #e6f6e6; */
        border: none;
        transition: all 0.3s ease;
    }

    #clear:hover,
    #confirm:hover {
        transform: translateY(-1px);
        cursor: pointer;
        /* box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); */
    }

    #clear svg {
        width: 20px;
        height: 20px;
        fill: #e74c3c;
    }
</style> --}}

{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signature-pad');
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let selectedColor = '#070707';

        // Color selection logic
        const colorOptions = document.querySelectorAll('.color-option');
        colorOptions.forEach(option => {
            option.addEventListener('click', () => {
                colorOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                selectedColor = option.dataset.color;
                ctx.strokeStyle = selectedColor;
            });
        });

        // Handle high DPI displays
        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        ctx.scale(dpr, dpr);

        // Initial styling
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = selectedColor;

        function getCanvasPosition(e) {
            const rect = canvas.getBoundingClientRect();
            let x, y;

            if (e.touches) {
                x = e.touches[0].clientX - rect.left;
                y = e.touches[0].clientY - rect.top;
            } else {
                x = e.clientX - rect.left;
                y = e.clientY - rect.top;
            }

            return {
                x: x * (canvas.width / rect.width / dpr),
                y: y * (canvas.height / rect.height / dpr)
            };
        }

        function startDrawing(e) {
            drawing = true;
            const pos = getCanvasPosition(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!drawing) return;
            const pos = getCanvasPosition(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDrawing() {
            drawing = false;
        }

        // Event listeners
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Touch support
        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            startDrawing(e);
        });
        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            draw(e);
        });
        canvas.addEventListener('touchend', stopDrawing);

        // Clear button
        document.getElementById('clear').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            const signatureInput = document.getElementById('signature-input');
            if (signatureInput) {
                signatureInput.value = '';
            }
            canvas.style.borderColor = '#d3d3d3';
        });

        // Blank canvas reference
        const blankCanvas = document.createElement('canvas');
        blankCanvas.width = canvas.width;
        blankCanvas.height = canvas.height;

        // Confirm signature
        document.getElementById('confirm').addEventListener('click', () => {
            if (canvas.toDataURL() === blankCanvas.toDataURL()) {
                alert('Fournir une signature d\'abord !');
                return;
            }

            const tempCanvas = document.createElement('canvas');
            const tempCtx = tempCanvas.getContext('2d');

            tempCanvas.width = canvas.width;
            tempCanvas.height = canvas.height;

            tempCtx.fillStyle = '#ffffff';
            tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
            tempCtx.drawImage(canvas, 0, 0);

            const imageData = tempCanvas.toDataURL('image/jpeg', 0.8);

            let signatureInput = document.getElementById('signature-input');
            if (!signatureInput) {
                signatureInput = document.createElement('input');
                signatureInput.type = 'hidden';
                signatureInput.id = 'signature-input';
                signatureInput.name = 'signature';
                document.querySelector('.signature-container').appendChild(signatureInput);
            }

            signatureInput.value = imageData;
            canvas.style.borderColor = '#2ecc71';
        });
    });
</script>
 --}}

{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signature-pad');
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let selectedColor = '#070707';

        // Color selection logic
        const colorOptions = document.querySelectorAll('.color-option');
        colorOptions.forEach(option => {
            option.addEventListener('click', () => {
                colorOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                selectedColor = option.dataset.color;
                ctx.strokeStyle = selectedColor;
            });
        });

        // Handle high DPI displays
        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        ctx.scale(dpr, dpr);

        // Initial styling
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = selectedColor;

        function getCanvasPosition(e) {
            const rect = canvas.getBoundingClientRect();
            let x, y;

            if (e.touches) {
                x = e.touches[0].clientX - rect.left;
                y = e.touches[0].clientY - rect.top;
            } else {
                x = e.clientX - rect.left;
                y = e.clientY - rect.top;
            }

            return {
                x: x * (canvas.width / rect.width / dpr),
                y: y * (canvas.height / rect.height / dpr)
            };
        }

        function startDrawing(e) {
            drawing = true;
            const pos = getCanvasPosition(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!drawing) return;
            const pos = getCanvasPosition(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDrawing() {
            drawing = false;
        }

        // Event listeners
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Touch support
        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            startDrawing(e);
        });
        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            draw(e);
        });
        canvas.addEventListener('touchend', stopDrawing);

        // Clear button
        document.getElementById('clear').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            const signatureInput = document.getElementById('signature-input');
            if (signatureInput) {
                signatureInput.value = '';
            }
            canvas.style.borderColor = '#d3d3d3';
        });

        // Blank canvas reference
        const blankCanvas = document.createElement('canvas');
        blankCanvas.width = canvas.width;
        blankCanvas.height = canvas.height;

        // Confirm signature (PNG version)
        document.getElementById('confirm').addEventListener('click', () => {
            if (canvas.toDataURL() === blankCanvas.toDataURL()) {
                alert('Fournir une signature d\'abord !');
                return;
            }

            const tempCanvas = document.createElement('canvas');
            const tempCtx = tempCanvas.getContext('2d');

            tempCanvas.width = canvas.width;
            tempCanvas.height = canvas.height;

            // For transparent background (remove fillRect)
            // For white background: 
            tempCtx.fillStyle = '#ffffff';
            tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);

            tempCtx.drawImage(canvas, 0, 0);

            // Save as PNG
            const imageData = tempCanvas.toDataURL('image/png');

            let signatureInput = document.getElementById('signature-input');
            if (!signatureInput) {
                signatureInput = document.createElement('input');
                signatureInput.type = 'hidden';
                signatureInput.id = 'signature-input';
                signatureInput.name = 'signature';
                document.querySelector('.signature-container').appendChild(signatureInput);
            }

            signatureInput.value = imageData;
            canvas.style.borderColor = '#2ecc71';
        });
    });
</script> --}}

<div class="signature-container">
    @error('signature')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    <input type="hidden" type="file" accept="image/*" id="signature-input" name="signature" />

    <canvas id="signature-pad" class="@error('signature') border-red-500 @enderror"></canvas>
    <div class="button-container">
        <div class="left-buttons">
            <span id="clear" aria-label="Supprimer">
                effacer
                <svg viewBox="0 0 24 24" width="20" height="20">
                    <path fill="currentColor"
                        d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
                </svg>
            </span>
            <span id="confirm">valider &check;</span>
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
</div>

<style>
    .signature-container {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        width: 100%;
    }

    .signature-container canvas {
        width: 100%;
        height: 150px;
        border: 2px dotted #d3d3d3;
        background-color: transparent;
        cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>') 0 16, auto;
        touch-action: none;
    }

    .button-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        gap: 15px;
    }

    .left-buttons {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .color-options {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .color-option {
        cursor: pointer;
        padding: 3px;
        border: 2px solid transparent;
        border-radius: 50%;
        transition: all 0.2s ease;
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

    #clear {
        color: #e74c3c;
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 8px 12px;
        border-radius: 5px;
        border: none;
        transition: all 0.3s ease;
    }

    #confirm {
        color: #2ecc71;
        padding: 8px 12px;
        border-radius: 5px;
        border: none;
        transition: all 0.3s ease;
    }

    #clear:hover,
    #confirm:hover {
        transform: translateY(-1px);
        cursor: pointer;
    }

    #clear svg {
        width: 20px;
        height: 20px;
        fill: #e74c3c;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signature-pad');
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let selectedColor = '#070707';

        // Color selection
        const colorOptions = document.querySelectorAll('.color-option');
        colorOptions.forEach(option => {
            option.addEventListener('click', () => {
                colorOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                selectedColor = option.dataset.color;
                ctx.strokeStyle = selectedColor;
            });
        });

        // High DPI handling
        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        ctx.scale(dpr, dpr);

        // Initial canvas setup
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = selectedColor;

        function getCanvasPosition(e) {
            const rect = canvas.getBoundingClientRect();
            let x, y;

            if (e.touches) {
                x = e.touches[0].clientX - rect.left;
                y = e.touches[0].clientY - rect.top;
            } else {
                x = e.clientX - rect.left;
                y = e.clientY - rect.top;
            }

            return {
                x: x * (canvas.width / rect.width / dpr),
                y: y * (canvas.height / rect.height / dpr)
            };
        }

        // Drawing functions
        function startDrawing(e) {
            drawing = true;
            const pos = getCanvasPosition(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!drawing) return;
            const pos = getCanvasPosition(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDrawing() {
            drawing = false;
        }

        // Event listeners
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Touch support
        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            startDrawing(e);
        });
        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            draw(e);
        });
        canvas.addEventListener('touchend', stopDrawing);

        // Clear functionality
        document.getElementById('clear').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signature-input').value = '';
            canvas.style.borderColor = '#d3d3d3';
        });

        // Confirm signature with transparent PNG
        document.getElementById('confirm').addEventListener('click', () => {
            const blankCanvas = document.createElement('canvas');
            blankCanvas.width = canvas.width;
            blankCanvas.height = canvas.height;

            if (canvas.toDataURL() === blankCanvas.toDataURL()) {
                alert('Fournir une signature d\'abord !');
                return;
            }

            // Create temporary canvas with transparency
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = canvas.width;
            tempCanvas.height = canvas.height;
            const tempCtx = tempCanvas.getContext('2d');

            // Preserve transparency by NOT filling with white
            tempCtx.drawImage(canvas, 0, 0);

            // Save as PNG with alpha channel
            const imageData = tempCanvas.toDataURL('image/png');
            document.getElementById('signature-input').value = imageData;
            canvas.style.borderColor = '#2ecc71';
        });
    });
</script>
