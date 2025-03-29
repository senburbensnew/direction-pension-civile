<div class="signature-container">
    <input type="hidden" id="signature-input" name="signature">
    <canvas id="signature-pad"></canvas>
    <div class="button-container">
        <span id="clear" aria-label="Supprimer">
            effacer
            <svg viewBox="0 0 24 24" width="20" height="20">
                <path fill="currentColor"
                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
            </svg>
        </span>
        <span id="confirm">valider &check;</span>
        <!-- New button to save the canvas as an image -->
        <span id="save-as-image">
            télécharger
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M12 3v9.26c-.61-.37-1.3-.63-2-.74V5.5h-4V9h2.5c.28 0 .5-.22.5-.5s-.22-.5-.5-.5H8V4.5c0-.28-.22-.5-.5-.5s-.5.22-.5.5V9H4V3h8zm6.7 7.3L12 21l-6.7-10.7L8 9h4V4h4v5h4l-2.3 3.3z" />
            </svg>
        </span>
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
            border: 2px dotted #d3d3d3;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            // width: 40px;
            height: 40px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            background-color: #f0f0f0;
        }

        #clear {
            background-color: #ffe6e6;
            color: #e74c3c;
        }

        #clear:hover {
            transform: translateY(-1px);
        }

        #clear svg {
            width: 20px;
            height: 20px;
            fill: #e74c3c;
        }

        #confirm {
            background-color: #e6f6e6;
            color: #2ecc71;
        }

        #confirm:hover {
            background-color: #d4efdf;
            transform: translateY(-1px);
        }

        #confirm svg {
            width: 20px;
            height: 20px;
            fill: #2ecc71;
        }

        /* Save button styling */
        #save-as-image {
            background-color: #d6e8f3;
            color: #3498db;
        }

        #save-as-image:hover {
            transform: translateY(-1px);
        }

        #save-as-image svg {
            width: 20px;
            height: 20px;
            fill: #3498db;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('signature-pad');
            const ctx = canvas.getContext('2d');
            let drawing = false;

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
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                const signatureInput = document.getElementById('signature-input');
                if (signatureInput) {
                    signatureInput.value = '';
                }
                canvas.style.borderColor = '#d3d3d3';
            });

            const blankCanvas = document.createElement('canvas');
            blankCanvas.width = canvas.width;
            blankCanvas.height = canvas.height;

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

                alert('Signature enregistrée avec succès !');
                canvas.style.borderColor = '#2ecc71';
            });

            document.getElementById('save-as-image').addEventListener('click', () => {
                if (canvas.toDataURL() === blankCanvas.toDataURL()) {
                    alert('Aucune signature à enregistrer !');
                    return;
                }

                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = 'signature.png';
                link.click();
            });
        });
    </script>
@endpush
