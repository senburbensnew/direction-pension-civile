import SignaturePad from 'signature_pad';
import './components/check-transfer-form.js';
import './components/rencontre-polling.js';
import { initTelephoneFields } from './validation/telephone.js';

function initSignaturePads() {
    document.querySelectorAll('[data-signature-pad]').forEach((container) => {
        if (container.dataset.signatureReady === '1') {
            return;
        }
        container.dataset.signatureReady = '1';

        const disabled = container.dataset.signatureDisabled === '1';
        const canvas = container.querySelector('canvas[data-signature-canvas]');
        const input = container.querySelector('input[data-signature-input]');
        if (!canvas || disabled) {
            return;
        }

        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
            penColor: '#070707',
        });

        const updateCanvasSize = () => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        };
        updateCanvasSize();

        const fieldKey = container.dataset.signaturePad;
        const colorOptions = container.querySelectorAll('[data-color]');
        colorOptions.forEach((option) => {
            option.addEventListener('click', () => {
                colorOptions.forEach((opt) => {
                    opt.classList.remove('border-gray-600');
                    opt.classList.add('border-transparent');
                });
                option.classList.remove('border-transparent');
                option.classList.add('border-gray-600');
                signaturePad.penColor = option.dataset.color;
            });
        });

        container.querySelector(`#clear-${fieldKey}`)?.addEventListener('click', () => {
            signaturePad.clear();
            if (input) {
                input.value = '';
            }
            canvas.style.borderColor = '#d3d3d3';
        });

        container.querySelector(`#confirm-${fieldKey}`)?.addEventListener('click', () => {
            if (signaturePad.isEmpty()) {
                alert("Veuillez fournir une signature d'abord !");
                return;
            }
            if (input) {
                input.value = signaturePad.toDataURL('image/png');
            }
            canvas.style.borderColor = '#2ecc71';
        });

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const data = signaturePad.toData();
                updateCanvasSize();
                signaturePad.fromData(data);
            }, 200);
        });
    });
}

export function initDomEnhancements() {
    initSignaturePads();
    initTelephoneFields();
}
