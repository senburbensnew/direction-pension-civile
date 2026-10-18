import Quill from 'quill';
import 'quill/dist/quill.snow.css';

function syncQuillToInput(quill, inputId) {
    const input = document.getElementById(inputId);
    if (!input) {
        return;
    }
    input.value = quill.root.innerHTML;
}

function initNewsletterCampaignEditor() {
    const editorEl = document.getElementById('newsletter-html-editor');
    const inputEl = document.getElementById('newsletter-html-body');
    if (!editorEl || !inputEl) {
        return;
    }

    const quill = new Quill('#newsletter-html-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'image'],
                ['clean'],
            ],
        },
    });

    if (inputEl.value) {
        quill.clipboard.dangerouslyPasteHTML(inputEl.value);
    }

    quill.on('text-change', () => syncQuillToInput(quill, 'newsletter-html-body'));
    const form = editorEl.closest('form');
    form?.addEventListener('submit', () => syncQuillToInput(quill, 'newsletter-html-body'));
}

initNewsletterCampaignEditor();
