function bootDocumentsUpload() {
    const form = document.getElementById('upload-form');
    const fileInput = document.getElementById('pdf_file');
    const uploadButton = document.getElementById('upload-button');
    const fileNameEl = document.getElementById('file-name');
    const spinner = document.getElementById('loading-spinner');
    const successToast = document.getElementById('toast-success');

    if (successToast) {
        setTimeout(() => {
            successToast.style.display = 'none';
        }, 5000);
    }

    if (!fileInput || !uploadButton) {
        return;
    }

    const syncFileUi = () => {
        const hasFile = fileInput.files.length > 0;
        uploadButton.disabled = !hasFile;
        uploadButton.classList.toggle('bg-gray-400', !hasFile);
        uploadButton.classList.toggle('cursor-not-allowed', !hasFile);
        uploadButton.classList.toggle('bg-green-600', hasFile);
        uploadButton.classList.toggle('hover:bg-green-700', hasFile);
        if (fileNameEl) {
            fileNameEl.textContent = hasFile ? fileInput.files[0].name : 'Aucun fichier sélectionné';
        }
    };

    fileInput.addEventListener('change', syncFileUi);
    syncFileUi();

    form?.addEventListener('submit', () => {
        spinner?.classList.remove('hidden');
        uploadButton.disabled = true;
        uploadButton.innerText = 'Téléchargement...';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootDocumentsUpload);
} else {
    bootDocumentsUpload();
}
