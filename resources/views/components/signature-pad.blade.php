<div class="signature-container">
    <input type="hidden" id="signature-input" name="signature">
    <canvas id="signature-pad"></canvas>
    <div class="button-container">
        <span id="clear" aria-label="Supprimer">
            <svg viewBox="0 0 24 24" width="20" height="20">
                <path fill="currentColor"
                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
            </svg>
        </span>
        <span id="confirm">&check;</span>
    </div>
</div>

@push('styles')
    <style>
        .signature-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .signature-container canvas {
            width: 100%;
            height: 150px;
            border: 2px dotted lightgray;
            background-color: #fff;
            cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>') 0 16, auto;
            touch-action: none;
        }

        .button-container {
            display: flex;
            gap: 10px;
            width: 100%;
        }

        .button-container span {
            width: 40px;
            /* Même largeur */
            height: 40px;
            /* Même hauteur */
            cursor: pointer;
            color: white;
            border: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            /* Padding réduit */
        }

        #clear {
            background-color: #e74c3c;
        }

        #clear:hover {
            background-color: #c0392b;
            transform: translateY(-1px);
        }

        #clear svg {
            width: 20px;
            height: 20px;
            fill: white;
            display: block;
            /* Centrage parfait */
            margin: 0 auto;
        }

        #confirm {
            background-color: #2ecc71;
        }

        #confirm:hover {
            background-color: #27ae60;
            transform: translateY(-1px);
        }

        #confirm svg {
            width: 24px;
            height: 24px;
            fill: white;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('signature-pad');
            const ctx = canvas.getContext('2d');
            let drawing = false;

            // Handle high-DPI displays
            const rect = canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.scale(dpr, dpr);

            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000';

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

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            canvas.addEventListener('touchstart', (e) => {
                e.preventDefault();
                startDrawing(e);
            });
            canvas.addEventListener('touchmove', (e) => {
                e.preventDefault();
                draw(e);
            });
            canvas.addEventListener('touchend', stopDrawing);

            document.getElementById('clear').addEventListener('click', () => {
                // Clear canvas
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                // Clear input value
                const signatureInput = document.getElementById('signature-input');
                if (signatureInput) {
                    signatureInput.value = '';
                }
            });

            const blankCanvas = document.createElement('canvas');
            blankCanvas.width = canvas.width;
            blankCanvas.height = canvas.height;

            document.getElementById('confirm').addEventListener('click', () => {
                if (canvas.toDataURL() === blankCanvas.toDataURL()) {
                    alert('Please provide a signature first');
                    return;
                }

                // Create temporary canvas with white background
                const tempCanvas = document.createElement('canvas');
                const tempCtx = tempCanvas.getContext('2d');

                tempCanvas.width = canvas.width;
                tempCanvas.height = canvas.height;

                // Fill with white background
                tempCtx.fillStyle = '#ffffff';
                tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
                tempCtx.drawImage(canvas, 0, 0);

                // Convert to JPEG with 80% quality
                const imageData = tempCanvas.toDataURL('image/jpeg', 0.8);

                // Create hidden input if it doesn't exist
                let signatureInput = document.getElementById('signature-input');
                if (!signatureInput) {
                    signatureInput = document.createElement('input');
                    signatureInput.type = 'hidden';
                    signatureInput.id = 'signature-input';
                    signatureInput.name = 'signature';
                    document.querySelector('.signature-container').appendChild(signatureInput);
                }

                // Set the input value
                signatureInput.value = imageData;

                // Optional: Submit form or show confirmation
                alert('Signature saved successfully!');
            });
        });
    </script>
@endpush
