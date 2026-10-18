document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('check-transfert-form');
    if (!form || form.dataset.checkTransferReady === '1') {
        return;
    }
    form.dataset.checkTransferReady = '1';

    form.addEventListener('submit', () => {
        const editableText = document.getElementById('editableText')?.innerText.trim() ?? '';
        const hiddenInput = document.getElementById('hiddenInput');
        if (hiddenInput) {
            hiddenInput.value = editableText;
        }
    });
});
