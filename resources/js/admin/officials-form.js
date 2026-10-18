import '../../css/admin/officials-form.css';
import Quill from 'quill';

const toolbarOptions = [
    [{ header: [2, 3, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['blockquote', 'link'],
    ['clean'],
];

function plainTextToHtml(text) {
    return text
        .trim()
        .split(/\n\n+/)
        .map((p) => `<p>${p.replace(/\n/g, '<br>')}</p>`)
        .join('');
}

function loadInitialContent(quill, raw) {
    if (!raw || !raw.trim()) {
        return;
    }

    const isHtml = /<[a-z][\s\S]*>/i.test(raw);
    if (isHtml) {
        quill.clipboard.dangerouslyPasteHTML(raw);
    } else {
        quill.clipboard.dangerouslyPasteHTML(plainTextToHtml(raw));
    }
}

function syncQuillToInput(quill, inputId) {
    const input = document.getElementById(inputId);
    if (!input) {
        return;
    }

    const html = quill.root.innerHTML;
    input.value = html === '<p><br></p>' ? '' : html;
}

function initEditor(editorId, inputId) {
    const editorEl = document.getElementById(editorId);
    const inputEl = document.getElementById(inputId);
    if (!editorEl || !inputEl) {
        return null;
    }

    const quill = new Quill(`#${editorId}`, {
        theme: 'snow',
        placeholder:
            editorId === 'editor-biographie'
                ? 'Rédigez la biographie ici…'
                : 'Rédigez le discours ici…',
        modules: { toolbar: toolbarOptions },
    });

    loadInitialContent(quill, inputEl.value);

    quill.on('text-change', () => syncQuillToInput(quill, inputId));

    const form = editorEl.closest('form');
    form?.addEventListener('submit', () => syncQuillToInput(quill, inputId));

    return quill;
}

function scrollToHashFragment() {
    if (!window.location.hash) {
        return;
    }

    const target = document.querySelector(window.location.hash);
    if (target) {
        setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 150);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('editor-discours')) {
        return;
    }

    initEditor('editor-biographie', 'input-biographie');
    initEditor('editor-discours', 'input-discours');
    scrollToHashFragment();
});
